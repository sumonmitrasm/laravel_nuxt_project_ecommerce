<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->wishlists()
            ->with([
                'product.category:id,category_name,category_discount',
                'product.variants' => fn ($query) => $query->where('status', true)->select('id', 'product_id', 'price', 'stock'),
            ])
            ->latest('id')->get()
            ->filter(fn ($item) => $item->product && $item->product->status)
            ->map(fn ($item) => $this->itemData($item->id, $item->product))
            ->values();

        return response()->json(['status' => true, 'count' => $items->count(), 'items' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['product_id' => ['required', 'integer', 'exists:products,id']]);
        $product = Product::query()->whereKey($data['product_id'])->where('status', true)->firstOrFail();
        $item = $request->user()->wishlists()->firstOrCreate(['product_id' => $product->id]);

        return response()->json([
            'status' => true,
            'message' => $item->wasRecentlyCreated ? 'Product added to your wishlist.' : 'Product is already in your wishlist.',
            'count' => $request->user()->wishlists()->count(),
        ], $item->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, int $product): JsonResponse
    {
        $request->user()->wishlists()->where('product_id', $product)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product removed from your wishlist.',
            'count' => $request->user()->wishlists()->count(),
        ]);
    }

    private function itemData(int $wishlistId, Product $product): array
    {
        $variants = $product->variants;
        $regularPrice = $variants->isNotEmpty()
            ? $variants->map(fn ($variant) => (float) $product->regularPriceForVariant($variant))->min()
            : (float) $product->product_price;
        $stock = $variants->isEmpty() ? null : (int) $variants->sum('stock');

        return [
            'id' => $wishlistId,
            'product_id' => $product->id,
            'name' => $product->product_name,
            'category_name' => $product->category?->category_name ?? 'Products',
            'image_url' => $product->image_url,
            'regular_price' => number_format($regularPrice, 2, '.', ''),
            'final_price' => $product->discountedPrice($regularPrice),
            'has_discount' => $product->effective_discount > 0,
            'stock' => $stock,
            'in_stock' => $stock === null || $stock > 0,
        ];
    }
}