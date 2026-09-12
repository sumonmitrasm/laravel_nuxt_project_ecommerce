<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['low', 'out', 'healthy'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
        ]);

        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? '';

        $variants = ProductVariant::query()
            ->with('product:id,product_name,product_image')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('sku', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($product) =>
                            $product->where('product_name', 'like', "%{$search}%")
                        );
                });
            })
            ->when($status === 'out', fn ($query) => $query->where('stock', 0))
            ->when($status === 'low', fn ($query) => $query
                ->whereColumn('stock', '<=', 'low_stock_threshold')
                ->where('stock', '>', 0))
            ->when($status === 'healthy', fn ($query) => $query
                ->whereColumn('stock', '>', 'low_stock_threshold'))
            ->orderBy('stock')
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();

        $history = StockAdjustment::query()
            ->with('variant.product:id,product_name')
            ->latest()
            ->limit(15)
            ->get();

        return view('admin.inventory.index', compact('variants', 'history'));
    }

    public function adjust(
        Request $request,
        ProductVariant $variant,
        InventoryService $inventory,
    ) {
        $data = $request->validate([
            'change_quantity' => ['required', 'integer', 'not_in:0', 'between:-1000000,1000000'],
            'reason' => ['required', Rule::in(['purchase', 'correction', 'damage', 'return', 'other'])],
            'note' => ['nullable', 'string', 'max:500'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        $variant->update([
            'low_stock_threshold' => $data['low_stock_threshold'],
        ]);

        $inventory->manualAdjust(
            variantId: $variant->id,
            quantity: $data['change_quantity'],
            reason: $data['reason'],
            adminId: Auth::guard('admin')->id(),
            note: $data['note'] ?? null,
        );

        return response()->json([
            'message' => 'Stock updated and history recorded.',
        ]);
    }
}