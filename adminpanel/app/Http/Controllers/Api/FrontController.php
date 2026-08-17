<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Section;
use App\Support\ShopFilterCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FrontController extends Controller
{
    public function menu(): JsonResponse
    {
        $sections = Cache::remember(
            'api.sections-with-categories.v5',
            now()->addHours(6),
            fn () => Section::sections(),
        );

        return response()->json([
            'status' => true,
            'categories' => $sections,
        ], 200);
    }

    public function listing(Request $request, string $url): JsonResponse
    {
        $categoryDetails = Category::categoryDetails($url);

        if (! $categoryDetails) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        $brandIds = $this->selectedBrandIds($request);

        $products = Product::with(['brand:id,name', 'category:id,category_name,category_discount'])
            ->whereIn('category_id', $categoryDetails['catIds'])
            ->where('status', true)
            ->when($brandIds !== [], fn ($query) => $query->whereIn('brand_id', $brandIds))
            ->latest('id')
            ->paginate(8);

        return response()->json([
            'status' => true,
            'categoryDetails' => $categoryDetails['categoryDetails'],
            'breadcrumbs' => $categoryDetails['breadcrumbs'],
            'filters' => [
                'brands' => $this->availableBrands($categoryDetails['catIds']),
            ],
            'products' => $products->items(),
            'pagination' => $this->paginationData($products),
        ], 200);
    }

    public function products(Request $request): JsonResponse
    {
        $brandIds = $this->selectedBrandIds($request);

        $products = Product::with(['brand:id,name', 'category:id,category_name,category_discount'])
            ->where('status', true)
            ->when($brandIds !== [], fn ($query) => $query->whereIn('brand_id', $brandIds))
            ->latest('id')
            ->paginate(8);

        return response()->json([
            'status' => true,
            'categoryDetails' => null,
            'breadcrumbs' => [],
            'filters' => [
                'brands' => $this->availableBrands(),
            ],
            'products' => $products->items(),
            'pagination' => $this->paginationData($products),
        ], 200);
    }

    private function selectedBrandIds(Request $request): array
    {
        $brands = $request->query('brand', '');
        $brands = is_array($brands) ? $brands : explode(',', (string) $brands);

        return collect($brands)
            ->map(fn ($brandId) => filter_var($brandId, FILTER_VALIDATE_INT))
            ->filter(fn ($brandId) => $brandId !== false && $brandId > 0)
            ->unique()
            ->take(50)
            ->values()
            ->all();
    }

    private function availableBrands(array $categoryIds = []): array
    {
        sort($categoryIds);
        $scope = $categoryIds === [] ? 'all' : sha1(implode(',', $categoryIds));
        $version = ShopFilterCache::version();

        return Cache::remember("api.shop-filter.brands.{$scope}.v{$version}", now()->addMinutes(120), function () use ($categoryIds) {
            return Brand::query()
                ->select(['brands.id', 'brands.name'])
                ->selectRaw('COUNT(DISTINCT products.id) AS product_count')
                ->join('products', 'products.brand_id', '=', 'brands.id')
                ->where('brands.status', true)
                ->where('products.status', true)
                ->when($categoryIds !== [], fn ($query) => $query->whereIn('products.category_id', $categoryIds))
                ->groupBy('brands.id', 'brands.name')
                ->orderBy('brands.name')
                ->get()
                ->map(fn ($brand) => [
                    'id' => (int) $brand->id,
                    'name' => $brand->name,
                    'product_count' => (int) $brand->product_count,
                ])
                ->all();
        });
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

        $productData = $product->toArray();
        $productData['variants'] = $product->variants->map(function ($variant) use ($product) {
            $regularPrice = $product->regularPriceForVariant($variant);

            return [
                ...$variant->only(['id', 'product_id', 'sku', 'price', 'stock', 'status']),
                'uses_base_price' => $variant->price === null,
                'regular_price' => $regularPrice,
                'effective_discount' => $product->effective_discount,
                'final_price' => $product->discountedPrice((float) $regularPrice),
                'values' => $variant->values,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'product' => $productData,
        ], 200);
    }
}
