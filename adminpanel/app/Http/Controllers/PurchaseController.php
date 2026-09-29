<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    public function suppliers()
    {
        $suppliers = Supplier::query()
            ->withSum('purchases as total_purchased', 'total_amount')
            ->withSum('purchases as total_due', 'due_amount')
            ->latest()->paginate(20);

        return view('admin.purchase.suppliers', compact('suppliers'));
    }

    public function storeSupplier(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);
        Supplier::create($data);
        if ($request->expectsJson()) return response()->json(['message' => 'Supplier added successfully.']);
        return back()->with('success', 'Supplier added successfully.');
    }

    public function editSupplier(Supplier $supplier)
    {
        return view('admin.purchase.edit-supplier', compact('supplier'));
    }

    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'boolean'],
        ]);
        $supplier->update($data);
        if ($request->expectsJson()) return response()->json(['message' => 'Supplier updated successfully.', 'redirect_url' => route('suppliers.index')]);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function index()
    {
        $purchases = Purchase::query()->with('supplier:id,name')->latest('purchase_date')->paginate(20);
        return view('admin.purchase.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::query()->where('status', true)->orderBy('name')->get(['id', 'name']);
        $variants = ProductVariant::query()->with('product:id,product_name')->where('status', true)->orderBy('sku')->get();
        return view('admin.purchase.create', compact('suppliers', 'variants'));
    }

    public function store(Request $request, InventoryService $inventory)
    {
        $data = $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')],
            'purchase_date' => ['required', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer', 'distinct', Rule::exists('product_variants', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $purchase = DB::transaction(function () use ($data, $inventory) {
            $variants = ProductVariant::query()->with('product:id,product_name')->lockForUpdate()
                ->whereIn('id', collect($data['items'])->pluck('variant_id'))->get()->keyBy('id');
            $total = 0;
            foreach ($data['items'] as $item) $total += $item['quantity'] * $item['unit_cost'];
            $paid = min((float) ($data['paid_amount'] ?? 0), $total);
            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'admin_id' => Auth::guard('admin')->id(),
                'purchase_number' => 'PUR-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'purchase_date' => $data['purchase_date'],
                'total_amount' => $total,
                'paid_amount' => $paid,
                'due_amount' => $total - $paid,
                'note' => $data['note'] ?? null,
            ]);
            foreach ($data['items'] as $item) {
                $variant = $variants[$item['variant_id']];
                $lineTotal = $item['quantity'] * $item['unit_cost'];
                $purchase->items()->create([
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product?->product_name ?? 'Product',
                    'sku' => $variant->sku,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $lineTotal,
                ]);
                $variant->update(['cost_price' => $item['unit_cost']]);
                $inventory->receivePurchase($variant, (int) $item['quantity'], $purchase, Auth::guard('admin')->id());
            }
            if ($paid > 0) {
                $purchase->payments()->create([
                    'supplier_id' => $purchase->supplier_id,
                    'admin_id' => Auth::guard('admin')->id(),
                    'amount' => $paid,
                    'paid_at' => $purchase->purchase_date,
                    'note' => 'Initial payment',
                ]);
            }
            return $purchase;
        }, 3);

        if ($request->expectsJson()) return response()->json(['message' => 'Purchase saved and stock updated.', 'redirect_url' => route('purchases.show', $purchase)]);
        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase saved and stock updated.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.variant.product', 'payments' => fn ($query) => $query->latest('paid_at')]);
        return view('admin.purchase.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load('items');
        return view('admin.purchase.edit', compact('purchase'));
    }

    public function update(Request $request, Purchase $purchase, InventoryService $inventory)
    {
        $data = $request->validate([
            'purchase_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', Rule::exists('purchase_items', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);
        DB::transaction(function () use ($purchase, $data, $inventory) {
            $purchase = Purchase::query()->lockForUpdate()->findOrFail($purchase->id);
            $items = $purchase->items()->lockForUpdate()->get()->keyBy('id');
            $submittedIds = collect($data['items'])->pluck('id')->map(fn ($id) => (int) $id)->sort()->values();
            if ($submittedIds->all() !== $items->keys()->sort()->values()->all()) {
                abort(422, 'Purchase products cannot be added or removed during an edit.');
            }

            $total = 0;
            foreach ($data['items'] as $itemData) {
                $item = $items[$itemData['id']];
                $newQuantity = (int) $itemData['quantity'];
                $newCost = (float) $itemData['unit_cost'];
                $total += $newQuantity * $newCost;

                if ($newQuantity !== (int) $item->quantity) {
                    $variant = ProductVariant::query()->lockForUpdate()->findOrFail($item->product_variant_id);
                    $inventory->correctPurchaseQuantity(
                        $variant,
                        $newQuantity - (int) $item->quantity,
                        $purchase,
                        Auth::guard('admin')->id(),
                    );
                }
                $item->update(['quantity' => $newQuantity, 'unit_cost' => $newCost, 'line_total' => $newQuantity * $newCost]);
                ProductVariant::query()->whereKey($item->product_variant_id)->update(['cost_price' => $newCost]);
            }
            if ((float) $purchase->paid_amount > $total) abort(422, 'New total cannot be less than the amount already paid.');
            $purchase->update([
                'purchase_date' => $data['purchase_date'],
                'note' => $data['note'] ?? null,
                'total_amount' => $total,
                'due_amount' => $total - (float) $purchase->paid_amount,
            ]);
        }, 3);
        if ($request->expectsJson()) return response()->json(['message' => 'Purchase information updated successfully.', 'redirect_url' => route('purchases.show', $purchase)]);
        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase information updated successfully.');
    }

    public function storePayment(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$purchase->due_amount],
            'paid_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);
        DB::transaction(function () use ($purchase, $data) {
            $purchase = Purchase::query()->lockForUpdate()->findOrFail($purchase->id);
            if ((float) $data['amount'] > (float) $purchase->due_amount) abort(422, 'Payment is greater than the due amount.');
            $purchase->payments()->create(['supplier_id' => $purchase->supplier_id, 'admin_id' => Auth::guard('admin')->id(), 'amount' => $data['amount'], 'paid_at' => $data['paid_at'], 'note' => $data['note'] ?? null]);
            $purchase->increment('paid_amount', $data['amount']);
            $purchase->decrement('due_amount', $data['amount']);
        }, 3);
        if ($request->expectsJson()) return response()->json(['message' => 'Supplier payment saved.']);
        return back()->with('success', 'Supplier payment saved.');
    }
}
