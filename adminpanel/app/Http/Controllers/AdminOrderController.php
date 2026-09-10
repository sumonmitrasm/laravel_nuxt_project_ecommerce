<?php

namespace App\Http\Controllers;

use App\Mail\OrderStatusMail;

use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminOrderController extends Controller
{
    private const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['processing', 'cancelled'],
        'processing' => ['shipped'],
        'shipped' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'order_status' => ['nullable', Rule::in(array_keys(self::TRANSITIONS))],
            'payment_status' => ['nullable', Rule::in(['unpaid', 'pending', 'paid', 'failed', 'cancelled', 'refund_pending', 'refunded'])],
            'payment_method' => ['nullable', Rule::in(['cod', 'sslcommerz'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));
        $orders = Order::query()
            ->with('user:id,name,email')
            ->withCount('items')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('address', fn ($address) => $address
                        ->where('recipient_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%"));
            }))
            ->when($filters['order_status'] ?? null, fn ($query, $status) => $query->where('order_status', $status))
            ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status))
            ->when($filters['payment_method'] ?? null, fn ($query, $method) => $query->where('payment_method', $method))
            ->latest('placed_at')
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();

        $counts = Order::query()->selectRaw('order_status, COUNT(*) as total')
            ->groupBy('order_status')->pluck('total', 'order_status');

        return view('admin.order.index', [
            'title' => 'Orders',
            'orders' => $orders,
            'counts' => $counts,
        ]);
    }

    public function show(Order $order)
    {
        $order->load([
            'user:id,name,email', 'coupon:id,coupon_name,coupon_code', 'items',
            'address', 'payments' => fn ($query) => $query->latest(),
            'statusHistories' => fn ($query) => $query->oldest(),
        ]);

        return view('admin.order.show', [
            'title' => 'Order '.$order->order_number,
            'order' => $order,
            'nextStatuses' => self::TRANSITIONS[$order->order_status] ?? [],
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::TRANSITIONS))],
            'note' => ['nullable', 'required_if:status,cancelled', 'string', 'max:500'],
        ]);

        $updatedOrder = DB::transaction(function () use ($order, $validated) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $allowed = self::TRANSITIONS[$lockedOrder->order_status] ?? [];

            if (! in_array($validated['status'], $allowed, true)) {
                throw ValidationException::withMessages(['status' => 'This order status change is not allowed.']);
            }

            if ($lockedOrder->payment_method === 'sslcommerz'
                && $lockedOrder->payment_status !== 'paid'
                && $validated['status'] !== 'cancelled') {
                throw ValidationException::withMessages(['status' => 'Online payment must be verified before processing this order.']);
            }

            if ($validated['status'] === 'cancelled') {
                $lockedOrder->load('items');
                foreach ($lockedOrder->items as $item) {
                    if ($item->product_variant_id) {
                        ProductVariant::query()->whereKey($item->product_variant_id)
                            ->lockForUpdate()->first()?->increment('stock', $item->quantity);
                    }
                }

                $paymentStatus = $lockedOrder->payment_status === 'paid' ? 'refund_pending' : 'cancelled';
                $lockedOrder->payments()->update(['status' => $paymentStatus]);
                CouponUsage::where('order_id', $lockedOrder->id)->delete();
                $lockedOrder->payment_status = $paymentStatus;
                $lockedOrder->cancellation_reason = $validated['note'] ?: 'Cancelled by admin.';
                $lockedOrder->cancelled_at = now();
            }

            $lockedOrder->order_status = $validated['status'];
            $lockedOrder->save();
            $lockedOrder->statusHistories()->create([
                'status' => $validated['status'],
                'note' => $validated['note'] ?: 'Status updated by admin.',
                'changed_by_type' => 'admin',
                'changed_by_id' => Auth::guard('admin')->id(),
            ]);

            return $lockedOrder;
        }, 3);

        if (in_array($updatedOrder->order_status, ['delivered', 'cancelled'], true)) {
            try {
                Mail::to($updatedOrder->user->email)->send(
                    new OrderStatusMail($updatedOrder, $updatedOrder->order_status)
                );
            } catch (\Throwable $exception) {
                Log::error('Order status email could not be sent.', [
                    'order_id' => $updatedOrder->id,
                    'status' => $updatedOrder->order_status,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Order status updated successfully.']);
        }

        return back()->with('success', 'Order status updated successfully.');
    }
}
