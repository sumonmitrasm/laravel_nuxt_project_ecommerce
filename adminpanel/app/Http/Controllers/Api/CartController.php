<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    private const MAX_ITEM_QUANTITY = 3;

    public function index(Request $request): JsonResponse
    {
        $guestToken = $this->validGuestToken($request, false);

        if (! $guestToken) {
            return response()->json($this->emptyCartPayload());
        }

        $cart = Cart::query()
            ->whereNull('user_id')
            ->where('guest_token', $guestToken)
            ->first();

        return response()->json($this->cartPayload($cart));
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

        $guestToken = $this->validGuestToken($request);

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
            $guestToken,
            $product,
            $variant,
            $validated
        ) {
            $cart = Cart::query()->firstOrCreate([
                'user_id' => null,
                'guest_token' => $guestToken,
            ]);

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
            ...$this->cartPayload($cart->fresh()),
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

        $guestToken = $this->validGuestToken($request);
        $cart = $this->guestCart($guestToken);

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
            ...$this->cartPayload($cart->fresh()),
            'message' => 'Cart updated successfully.',
        ]);
    }

    public function destroy(
        Request $request,
        CartItem $item
    ): JsonResponse {
        $guestToken = $this->validGuestToken($request);
        $cart = $this->guestCart($guestToken);

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
            ...$this->cartPayload($cart->fresh()),
            'message' => 'Product removed from cart.',
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $guestToken = $this->validGuestToken($request);
        $cart = $this->guestCart($guestToken, false);

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

    private function validGuestToken(
        Request $request,
        bool $required = true
    ): ?string {
        $guestToken = $request->header('X-Guest-Cart-Token');

        if (! $guestToken && ! $required) {
            return null;
        }

        if (! $guestToken || ! Str::isUuid($guestToken)) {
            throw ValidationException::withMessages([
                'guest_token' => 'A valid guest cart token is required.',
            ]);
        }

        return $guestToken;
    }

    private function guestCart(
        string $guestToken,
        bool $required = true
    ): ?Cart {
        $cart = Cart::query()
            ->whereNull('user_id')
            ->where('guest_token', $guestToken)
            ->first();

        if (! $cart && $required) {
            throw ValidationException::withMessages([
                'cart' => 'Cart could not be found.',
            ]);
        }

        return $cart;
    }

    private function ensureItemBelongsToCart(
        CartItem $item,
        Cart $cart
    ): void {
        if ((int) $item->cart_id !== (int) $cart->id) {
            abort(404);
        }
    }

    private function cartPayload(?Cart $cart): array
    {
        if (! $cart) {
            return $this->emptyCartPayload();
        }

        $cart->load([
            'items' => fn ($query) => $query->latest('id'),
            'items.product:id,category_id,product_name,product_code,product_price,product_discount,product_image,status',
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

        return [
            'status' => true,
            'cart_id' => $cart->id,
            'cart_count' => (int) $items->sum('quantity'),
            'items_count' => $items->count(),
            'items' => $items,
            'summary' => [
                'subtotal' => $subtotal,
                'discount' => 0,
                'shipping' => 0,
                'total' => $subtotal,
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
            'summary' => [
                'subtotal' => 0,
                'discount' => 0,
                'shipping' => 0,
                'total' => 0,
            ],
        ];
    }
}
