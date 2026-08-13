<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Section;
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

        $products = Product::with(['brand:id,name', 'category:id,category_discount'])
            ->whereIn('category_id', $categoryDetails['catIds'])
            ->where('status', true)
            ->latest('id')
            ->get();

        return response()->json([
            'status' => true,
            'categoryDetails' => $categoryDetails['categoryDetails'],
            'breadcrumbs' => $categoryDetails['breadcrumbs'],
            'products' => $products,
        ], 200);
    }

    public function details(int $id): JsonResponse
    {
        $product = Product::with([
                'section:id,name',
                'category:id,category_name,url,category_discount',
                'brand:id,name',
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
