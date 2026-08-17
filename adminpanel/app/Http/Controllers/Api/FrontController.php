<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Section;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class FrontController extends Controller
{
    public function menu(): JsonResponse
    {
        $sections = Cache::remember(
            'api.sections-with-categories.v4',
            now()->addHours(6),
            fn () => Section::sections(),
        );

        return response()->json([
            'status' => true,
            'categories' => $sections,
        ], 200);
    }

    public function listing(string $url): JsonResponse
    {
        $categoryDetails = Category::categoryDetails($url);

        if (! $categoryDetails) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        $products = Product::with(['brand:id,name', 'category:id,category_name,category_discount'])
            ->whereIn('category_id', $categoryDetails['catIds'])
            ->where('status', true)
            ->latest('id')
            ->paginate(8);

        return response()->json([
            'status' => true,
            'categoryDetails' => $categoryDetails['categoryDetails'],
            'breadcrumbs' => $categoryDetails['breadcrumbs'],
            'products' => $products->items(),
            'pagination' => $this->paginationData($products),
        ], 200);
    }

    public function products(): JsonResponse
    {
        $products = Product::with(['brand:id,name', 'category:id,category_name,category_discount'])
            ->where('status', true)
            ->latest('id')
            ->paginate(8);

        return response()->json([
            'status' => true,
            'categoryDetails' => null,
            'breadcrumbs' => [],
            'products' => $products->items(),
            'pagination' => $this->paginationData($products),
        ], 200);
    }

    private function paginationData(LengthAwarePaginator $products): array
    {
        return [
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'from' => $products->firstItem(),
            'to' => $products->lastItem(),
        ];
    }

    public function details(int $id): JsonResponse
    {
        $product = Product::with([
                'section:id,name',
                'category:id,category_name,url,category_discount',
                'brand:id,name',
                'variants' => fn ($query) => $query->where('status', true)
                    ->select('id', 'product_id', 'sku', 'price', 'stock', 'status'),
                'variants.values:id,attribute_id,value,color_code',
                'variants.values.attribute:id,name,slug,type',
            ])
            ->whereKey($id)
            ->where('status', true)
            ->first();

        if (! $product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'product' => $product,
        ], 200);
    }
}
