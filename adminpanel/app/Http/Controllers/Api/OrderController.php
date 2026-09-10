<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderPlacedMail;
use App\Models\Cart;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\UserAddress;
use App\Services\CartManager;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(private readonly CartManager $carts, private readonly CouponService $coupons) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()
            ->withCount('items')
            ->latest('id')
            ->get([
                'id', 'order_number', 'order_status', 'payment_status',
                'payment_method', 'grand_total', 'currency', 'placed_at',
            ]);

        return response()->json([
            'status' => true,
            'total_orders' => $orders->count(),
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $order = $request->user()->orders()
            ->where('order_number', $orderNumber)
            ->with([
                'items',
                'address',
                'payments' => fn ($query) => $query->latest('id'),
                'statusHistories' => fn ($query) => $query->oldest('id'),
            ])
            ->firstOrFail();

        $items = $order->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'product_code' => $item->product_code,
                'sku' => $item->sku,
                'image_url' => $item->image
                    ? asset('admin/productimage/'.basename($item->image))
                    : null,
                'options' => $item->options ?? [],
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ];
        });

        $payment = $order->payments->first();

        return response()->json([
            'status' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_status' => $order->order_status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'shipping_method_name' => $order->shipping_method_name,
                'subtotal' => $order->subtotal,
                'discount_amount' => $order->discount_amount,
                'shipping_charge' => $order->shipping_charge,
                'tax_amount' => $order->tax_amount,
                'grand_total' => $order->grand_total,
                'currency' => $order->currency,
                'customer_note' => $order->customer_note,
                'placed_at' => $order->placed_at,
                'items' => $items,
                'address' => $order->address,
                'payment' => $payment ? [
                    'method' => $payment->method,
                    'status' => $payment->status,
                    'transaction_id' => $payment->transaction_id,
                    'paid_at' => $payment->paid_at,
                ] : null,
                'status_histories' => $order->statusHistories->map(fn ($history) => [
                    'status' => $history->status,
                    'note' => $history->note,
                    'created_at' => $history->created_at,
                ]),
            ],
        ]);
    }
    public function cancel(Request $request, string $orderNumber): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $order = DB::transaction(function () use ($request, $orderNumber, $validated) {
            $order = Order::query()
                ->where('user_id', $request->user()->id)
                ->where('order_number', $orderNumber)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($order->order_status, ['pending', 'confirmed'], true)) {
                throw ValidationException::withMessages([
                    'order' => 'This order can no longer be cancelled.',
                ]);
            }

            $order->load('items');
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    ProductVariant::query()
                        ->whereKey($item->product_variant_id)
                        ->lockForUpdate()
                        ->first()
                        ?->increment('stock', $item->quantity);
                }
            }

            $paymentStatus = $order->payment_status === 'paid' ? 'refund_pending' : 'cancelled';
            $order->update([
                'order_status' => 'cancelled',
                'payment_status' => $paymentStatus,
                'cancellation_reason' => $validated['reason'],
                'cancelled_at' => now(),
            ]);
            $order->payments()->update(['status' => $paymentStatus]);
            $order->statusHistories()->create([
                'status' => 'cancelled',
                'note' => $validated['reason'],
                'changed_by_type' => 'user',
                'changed_by_id' => $request->user()->id,
            ]);
            CouponUsage::where('order_id', $order->id)->delete();

            return $order;
        }, 3);

        return response()->json([
            'status' => true,
            'message' => 'Your order has been cancelled.',
            'order' => [
                'order_status' => $order->order_status,
                'payment_status' => $order->payment_status,
                'cancellation_reason' => $order->cancellation_reason,
                'cancelled_at' => $order->cancelled_at,
            ],
        ]);
    }
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => ['required', 'integer'],
            'shipping_method_id' => ['required', 'integer'],
            'payment_method' => ['required', Rule::in(['cod', 'sslcommerz'])],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $user = $request->user();
        $address = UserAddress::with(['locationDivision', 'locationDistrict', 'locationUpazila'])
            ->where('user_id', $user->id)->where('status', true)->find($validated['address_id']);
        if (! $address) throw ValidationException::withMessages(['address_id' => 'Please select one of your active delivery addresses.']);

        $shipping = ShippingMethod::where('status', true)->find($validated['shipping_method_id']);
        if (! $shipping) throw ValidationException::withMessages(['shipping_method_id' => 'The selected shipping method is unavailable.']);

        $resolvedCart = $this->carts->resolve($request, false);
        if (! $resolvedCart) throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);

        $order = DB::transaction(function () use ($validated, $user, $address, $shipping, $resolvedCart) {
            $cart = Cart::whereKey($resolvedCart->id)->where('user_id', $user->id)->lockForUpdate()->first();
            if (! $cart) throw ValidationException::withMessages(['cart' => 'Your cart could not be found.']);
            $cart->load(['items', 'coupon']);
            if ($cart->items->isEmpty()) throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);

            $pricedItems = collect();
            foreach ($cart->items as $cartItem) {
                $product = Product::with('category:id,category_discount')->whereKey($cartItem->product_id)
                    ->where('status', true)->lockForUpdate()->first();
                if (! $product) throw ValidationException::withMessages(['cart' => 'A product in your cart is no longer available.']);

                $variant = null;
                if ($cartItem->product_variant_id) {
                    $variant = ProductVariant::with('values.attribute')->whereKey($cartItem->product_variant_id)
                        ->where('product_id', $product->id)->where('status', true)->lockForUpdate()->first();
                    if (! $variant || $variant->stock < $cartItem->quantity) {
                        throw ValidationException::withMessages(['cart' => "{$product->product_name} does not have enough stock."]);
                    }
                }

                $regularPrice = (float) $product->regularPriceForVariant($variant);
                $unitPrice = (float) $product->finalPriceForVariant($variant);
                $quantity = (int) $cartItem->quantity;
                $pricedItems->push([
                    'product' => $product, 'variant' => $variant, 'product_id' => $product->id,
                    'category_id' => $product->category_id, 'brand_id' => $product->brand_id,
                    'discount_percentage' => (float) $product->effective_discount,
                    'regular_price' => $regularPrice, 'unit_price' => $unitPrice, 'quantity' => $quantity,
                    'line_total' => round($unitPrice * $quantity, 2),
                    'options' => $variant?->values->map(fn ($value) => [
                        'name' => $value->attribute?->name, 'value' => $value->value,
                        'color_code' => $value->color_code,
                    ])->values()->all() ?? [],
                ]);
            }

            $subtotal = round((float) $pricedItems->sum('line_total'), 2);
            $discount = 0.0;
            $freeShipping = false;
            if ($cart->coupon) {
                $calculation = $this->coupons->calculate($cart->coupon, $pricedItems, $subtotal, $user, (string) $cart->guest_token);
                $discount = (float) $calculation['discount'];
                $freeShipping = (bool) $calculation['free_shipping'];
            }
            $shippingCharge = $freeShipping ? 0.0 : (float) $shipping->charge;
            $grandTotal = round(max(0, $subtotal - $discount + $shippingCharge), 2);

            $order = Order::create([
                'order_number' => $this->newOrderNumber(), 'user_id' => $user->id,
                'coupon_id' => $cart->coupon_id, 'shipping_method_id' => $shipping->id,
                'shipping_method_name' => $shipping->name, 'payment_method' => $validated['payment_method'],
                'payment_status' => 'unpaid', 'order_status' => 'pending', 'subtotal' => $subtotal,
                'discount_amount' => $discount, 'shipping_charge' => $shippingCharge, 'tax_amount' => 0,
                'grand_total' => $grandTotal, 'currency' => 'BDT',
                'customer_note' => $validated['customer_note'] ?? null, 'placed_at' => now(),
            ]);

            foreach ($pricedItems as $item) {
                $product = $item['product']; $variant = $item['variant'];
                $order->items()->create([
                    'product_id' => $product->id, 'product_variant_id' => $variant?->id,
                    'product_name' => $product->product_name, 'product_code' => $product->product_code,
                    'sku' => $variant?->sku, 'image' => $product->product_image, 'options' => $item['options'],
                    'quantity' => $item['quantity'], 'regular_price' => $item['regular_price'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => round(($item['regular_price'] - $item['unit_price']) * $item['quantity'], 2),
                    'line_total' => $item['line_total'],
                ]);
                if ($variant) $variant->decrement('stock', $item['quantity']);
            }

            $order->address()->create([
                'user_address_id' => $address->id, 'label' => $address->label,
                'recipient_name' => $address->recipient_name, 'phone' => $address->phone,
                'alternative_phone' => $address->alternative_phone, 'division_id' => $address->division,
                'district_id' => $address->district, 'upazila_id' => $address->upazila,
                'division_name' => $address->division_name, 'district_name' => $address->district_name,
                'upazila_name' => $address->upazila_name, 'area' => $address->area,
                'postal_code' => $address->postal_code, 'address_line' => $address->address_line,
            ]);
            $order->payments()->create([
                'method' => $validated['payment_method'], 'amount' => $grandTotal,
                'currency' => 'BDT', 'status' => 'pending',
            ]);
            $order->statusHistories()->create([
                'status' => 'pending', 'note' => 'Order placed by customer.',
                'changed_by_type' => 'user', 'changed_by_id' => $user->id,
            ]);
            if ($cart->coupon) {
                CouponUsage::create([
                    'coupon_id' => $cart->coupon->id, 'user_id' => $user->id, 'order_id' => $order->id,
                    'guest_token' => $cart->guest_token, 'discount_amount' => $discount, 'used_at' => now(),
                ]);
            }
            $cart->items()->delete();
            $cart->delete();
            return $order;
        }, 3);

        try {
            if ($order->payment_method === 'cod') {
                Mail::to($user->email)->send(new OrderPlacedMail($order));
            }
        } catch (\Throwable $exception) {
            Log::error('Order confirmation email could not be sent.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'exception' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'status' => true, 'message' => 'Order placed successfully.',
            'order' => $order->load(['items', 'address', 'payments']),
        ], 201);
    }

    private function newOrderNumber(): string
    {
        do $number = 'NC-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        while (Order::where('order_number', $number)->exists());
        return $number;
    }
}
