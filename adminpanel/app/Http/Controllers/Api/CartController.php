<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartManager;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    private const MAX_ITEM_QUANTITY = 3;

    public function __construct(
        private readonly CartManager $carts,
        private readonly CouponService $coupons,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cart = $this->carts->resolve($request, false);

        return response()->json($this->cartPayload($cart, $request));
    }
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'product_variant_id' => [
                'nullable',
                'integer',
                'exists:product_variants,id',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:'.self::MAX_ITEM_QUANTITY,
            ],
        ]);

        $cart = $this->carts->resolve($request, true);

        $product = Product::query()
            ->whereKey($validated['product_id'])
            ->where('status', true)
            ->firstOrFail();

        $variant = $this->resolveVariant(
            $product,
            $validated['product_variant_id'] ?? null
        );

        $this->validateStock(
            $variant,
            (int) $validated['quantity']
        );

        $cart = DB::transaction(function () use (
            $cart,
            $product,
            $variant,
            $validated
        ) {

            $cartItem = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->where('product_variant_id', $variant?->id)
                ->lockForUpdate()
                ->first();

            $newQuantity = ($cartItem?->quantity ?? 0)
                + (int) $validated['quantity'];

            if ($newQuantity > self::MAX_ITEM_QUANTITY) {
                throw ValidationException::withMessages([
                    'quantity' => 'A maximum of 3 units of the same item can be added.',
                ]);
            }

            $this->validateStock($variant, $newQuantity);

            if (! $cartItem) {
                $cartItem = new CartItem();
                $cartItem->cart_id = $cart->id;
                $cartItem->product_id = $product->id;
                $cartItem->product_variant_id = $variant?->id;
            }

            $cartItem->quantity = $newQuantity;
            $cartItem->save();

            return $cart;
        });

        return response()->json([
            ...$this->cartPayload($cart->fresh(), $request),
            'message' => 'Product added to cart.',
        ], 201);
    }

    public function update(
        Request $request,
        CartItem $item
    ): JsonResponse {
        $validated = $request->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:'.self::MAX_ITEM_QUANTITY,
            ],
        ]);

        $cart = $this->carts->resolve($request, true);

        $this->ensureItemBelongsToCart($item, $cart);

        $item->load([
            'product:id,status',
            'variant:id,product_id,stock,status',
        ]);

        if (! $item->product || ! $item->product->status) {
            throw ValidationException::withMessages([
                'item' => 'This product is no longer available.',
            ]);
        }

        if ($item->product_variant_id) {
            if (! $item->variant || ! $item->variant->status) {
                throw ValidationException::withMessages([
                    'item' => 'This product option is no longer available.',
                ]);
            }

            $this->validateStock(
                $item->variant,
                (int) $validated['quantity']
            );
        }

        $item->update([
            'quantity' => (int) $validated['quantity'],
        ]);

        return response()->json([
            ...$this->cartPayload($cart->fresh(), $request),
            'message' => 'Cart updated successfully.',
        ]);
    }

    public function destroy(
        Request $request,
        CartItem $item
    ): JsonResponse {
        $cart = $this->carts->resolve($request, true);

        $this->ensureItemBelongsToCart($item, $cart);

        $item->delete();

        if (! $cart->items()->exists()) {
            $cart->delete();

            return response()->json([
                ...$this->emptyCartPayload(),
                'message' => 'Product removed from cart.',
            ]);
        }

        return response()->json([
            ...$this->cartPayload($cart->fresh(), $request),
            'message' => 'Product removed from cart.',
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->carts->resolve($request, false);

        if ($cart) {
            DB::transaction(function () use ($cart) {
                $cart->items()->delete();
                $cart->delete();
            });
        }

        return response()->json([
            ...$this->emptyCartPayload(),
            'message' => 'Cart cleared successfully.',
        ]);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:100'],
        ]);

        $cart = $this->carts->resolve($request, true);
        $coupon = Coupon::query()
            ->where('code', strtoupper(trim($validated['code'])))
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'coupon' => 'The coupon code is invalid.',
            ]);
        }

        $cart->forceFill([
            'coupon_id' => $coupon->id,
            'coupon_applied_at' => now(),
        ])->save();

        try {
            $payload = $this->cartPayload($cart->fresh(), $request, true);
        } catch (ValidationException $exception) {
            $cart->forceFill([
                'coupon_id' => null,
                'coupon_applied_at' => null,
            ])->save();

            throw $exception;
        }

        return response()->json([
            ...$payload,
            'message' => 'Coupon applied successfully.',
        ]);
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $cart = $this->carts->resolve($request, true);

        $cart->forceFill([
            'coupon_id' => null,
            'coupon_applied_at' => null,
        ])->save();

        return response()->json([
            ...$this->cartPayload($cart->fresh(), $request),
            'message' => 'Coupon removed successfully.',
        ]);
    }

    private function resolveVariant(
        Product $product,
        ?int $variantId
    ): ?ProductVariant {
        $hasActiveVariants = $product->variants()
            ->where('status', true)
            ->exists();

        if ($hasActiveVariants && ! $variantId) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'Please select the product options.',
            ]);
        }

        if (! $variantId) {
            return null;
        }

        $variant = ProductVariant::query()
            ->whereKey($variantId)
            ->where('product_id', $product->id)
            ->where('status', true)
            ->first();

        if (! $variant) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'The selected product option is unavailable.',
            ]);
        }

        return $variant;
    }

    private function validateStock(
        ?ProductVariant $variant,
        int $quantity
    ): void {
        if ($variant && $quantity > $variant->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$variant->stock} item(s) are available.",
            ]);
        }
    }

    private function ensureItemBelongsToCart(
        CartItem $item,
        Cart $cart
    ): void {
        if ((int) $item->cart_id !== (int) $cart->id) {
            abort(404);
        }
    }

    private function cartPayload(?Cart $cart, Request $request, bool $strictCoupon = false): array
    {
        if (! $cart) {
            return $this->emptyCartPayload();
        }

        $cart->load([
            'items' => fn ($query) => $query->latest('id'),
            'coupon',
            'items.product:id,category_id,brand_id,product_name,product_code,product_price,product_discount,product_image,status',
            'items.product.category:id,category_discount',
            'items.variant:id,product_id,sku,price,stock,status',
            'items.variant.values:id,attribute_id,value,color_code',
            'items.variant.values.attribute:id,name,slug,type',
        ]);

        $items = $cart->items
            ->filter(fn (CartItem $item) => $item->product !== null)
            ->map(function (CartItem $item) {
                $product = $item->product;
                $variant = $item->variant;

                $unitPrice = (float) $product
                    ->finalPriceForVariant($variant);

                $regularPrice = (float) $product
                    ->regularPriceForVariant($variant);

                $quantity = (int) $item->quantity;

                return [
                    'id' => $item->id,
                    'product_id' => $product->id,
                    'category_id' => $product->category_id,
                    'brand_id' => $product->brand_id,
                    'product_variant_id' => $variant?->id,
                    'name' => $product->product_name,
                    'code' => $product->product_code,
                    'image_url' => $product->image_url,
                    'sku' => $variant?->sku,
                    'regular_price' => $regularPrice,
                    'unit_price' => $unitPrice,
                    'discount_percentage' => (float) $product->effective_discount,
                    'quantity' => $quantity,
                    'maximum_quantity' => $variant
                        ? min(self::MAX_ITEM_QUANTITY, $variant->stock)
                        : self::MAX_ITEM_QUANTITY,
                    'stock' => $variant?->stock,
                    'available' => (bool) $product->status
                        && (! $variant || $variant->status),
                    'options' => $variant?->values
                        ->map(fn ($value) => [
                            'name' => $value->attribute?->name,
                            'value' => $value->value,
                            'color_code' => $value->color_code,
                        ])
                        ->values()
                        ->all() ?? [],
                    'line_total' => round($unitPrice * $quantity, 2),
                ];
            })
            ->values();

        $subtotal = round(
            $items->sum('line_total'),
            2
        );

        $couponData = null;
        $discount = 0.0;
        $freeShipping = false;

        if ($cart->coupon) {
            try {
                $calculation = $this->coupons->calculate(
                    $cart->coupon,
                    $items,
                    $subtotal,
                    $request->user(),
                    (string) $cart->guest_token,
                );
                $discount = $calculation['discount'];
                $freeShipping = $calculation['free_shipping'];
                $couponData = [
                    'id' => $cart->coupon->id,
                    'code' => $cart->coupon->code,
                    'name' => $cart->coupon->name,
                    'discount_type' => $cart->coupon->discount_type,
                    'discount_value' => (float) $cart->coupon->discount_value,
                    'discount_amount' => $discount,
                    'free_shipping' => $freeShipping,
                ];
            } catch (ValidationException $exception) {
                if ($strictCoupon) {
                    throw $exception;
                }

                $cart->forceFill(['coupon_id' => null, 'coupon_applied_at' => null])->save();
            }
        }

        return [
            'status' => true,
            'cart_id' => $cart->id,
            'cart_count' => (int) $items->sum('quantity'),
            'items_count' => $items->count(),
            'items' => $items,
            'coupon' => $couponData,
            'summary' => [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping' => 0,
                'free_shipping' => $freeShipping,
                'total' => round(max(0, $subtotal - $discount), 2),
            ],
        ];
    }

    private function emptyCartPayload(): array
    {
        return [
            'status' => true,
            'cart_id' => null,
            'cart_count' => 0,
            'items_count' => 0,
            'items' => [],
            'coupon' => null,
            'summary' => [
                'subtotal' => 0,
                'discount' => 0,
                'shipping' => 0,
                'free_shipping' => false,
                'total' => 0,
            ],
        ];
    }
}
