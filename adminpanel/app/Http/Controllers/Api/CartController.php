<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $guestToken = $this->validGuestToken($request, false);

        if (! $guestToken) {
            return response()->json(['status' => true, 'cart_count' => 0, 'items' => []]);
        }

        $cart = Cart::query()->where('guest_token', $guestToken)->first();
        if (! $cart) {
            return response()->json(['status' => true, 'cart_count' => 0, 'items' => []]);
        }

        return response()->json([
            'status' => true,
            'cart_count' => (int) $cart->items()->sum('quantity'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     return response()->json([
    //     'received' => $request->all(),
    //     ]);
    // }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required','integer','exists:products,id',],
            'product_variant_id' => ['nullable','integer','exists:product_variants,id',],
            'quantity' => ['required', 'integer', 'min:1', 'max:3'],
        ]);
        $guestToken = $this->validGuestToken($request);
        $product = Product::query()
            ->whereKey($validated['product_id'])
            ->where('status', true)
            ->firstOrFail();
        $variant = null;
        if (! empty($validated['product_variant_id'])) {
            $variant = ProductVariant::query()
                ->whereKey($validated['product_variant_id'])
                ->where('product_id', $product->id)
                ->where('status', true)
                ->firstOrFail();
        }
        $hasActiveVariants = $product->variants()
            ->where('status', true)
            ->exists();
        if ($hasActiveVariants && ! $variant) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'Please select the product options.',
            ]);
        }
        if ($variant && $validated['quantity'] > $variant->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$variant->stock} item(s) are available.",
            ]);
        }
        $cartItem = DB::transaction(function () use (
            $guestToken,
            $product,
            $variant,
            $validated
        ) {
            $cart = Cart::firstOrCreate([
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
                + $validated['quantity'];

            if ($newQuantity > 3) {
                throw ValidationException::withMessages([
                    'quantity' => 'A maximum of 3 units of the same item can be added.',
                ]);
            }

            if ($variant && $newQuantity > $variant->stock) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$variant->stock} item(s) are available.",
                ]);
            }

            if (! $cartItem) {
                $cartItem = new CartItem();
                $cartItem->cart_id = $cart->id;
                $cartItem->product_id = $product->id;
                $cartItem->product_variant_id = $variant?->id;
            }

            $cartItem->quantity = $newQuantity;
            $cartItem->save();

            return $cartItem;
        });

        $cartItem->load([
            'product:id,product_name,product_price,product_image',
            'variant:id,product_id,sku,price,stock',
            'variant.values:id,attribute_id,value,color_code',
            'variant.values.attribute:id,name,slug,type',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Product added to cart.',
            'cart_item' => $cartItem,
            'cart_count' => (int) CartItem::where('cart_id', $cartItem->cart_id)->sum('quantity'),
        ], 201);
    }

    private function validGuestToken(Request $request, bool $required = true): ?string
    {
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Cart page development will be added in the next step.
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        // Cart page development will be added in the next step.
    }
}
