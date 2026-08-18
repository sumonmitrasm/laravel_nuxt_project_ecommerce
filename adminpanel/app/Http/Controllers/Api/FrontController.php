<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Section;
use App\Support\ShopFilterCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        $filterableAttributeIds = $this->filterableAttributeIds((int) $categoryDetails['categoryDetails']['id']);
        $attributeValueGroups = $this->selectedAttributeValueGroups($request, $filterableAttributeIds);

        $products = Product::with(['brand:id,name', 'category:id,category_name,category_discount'])
            ->whereIn('category_id', $categoryDetails['catIds'])
            ->where('status', true)
            ->when($brandIds !== [], fn ($query) => $query->whereIn('brand_id', $brandIds))
            ->when($attributeValueGroups !== [], function ($query) use ($attributeValueGroups) {
                $query->whereHas('variants', function ($variant) use ($attributeValueGroups) {
                    $variant->where('status', true);
                    foreach ($attributeValueGroups as $valueIds) {
                        $variant->whereHas('values', fn ($value) => $value->whereIn('attribute_values.id', $valueIds));
                    }
                });
            })
            ->latest('id')
            ->paginate(8);

        return response()->json([
            'status' => true,
            'categoryDetails' => $categoryDetails['categoryDetails'],
            'breadcrumbs' => $categoryDetails['breadcrumbs'],
            'filters' => [
                'brands' => $this->availableBrands($categoryDetails['catIds']),
                'attributes' => $this->availableAttributeFilters($categoryDetails['catIds'], $filterableAttributeIds),
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

    private function selectedAttributeValueGroups(Request $request, array $filterableAttributeIds): array
    {
        $values = $request->query('attribute', '');
        $values = is_array($values) ? $values : explode(',', (string) $values);
        $valueIds = collect($values)
            ->map(fn ($valueId) => filter_var($valueId, FILTER_VALIDATE_INT))
            ->filter(fn ($valueId) => $valueId !== false && $valueId > 0)
            ->unique()
            ->take(100)
            ->values();

        if ($valueIds->isEmpty() || $filterableAttributeIds === []) {
            return [];
        }

        return ProductAttributeValue::query()
            ->whereIn('id', $valueIds)
            ->whereIn('attribute_id', $filterableAttributeIds)
            ->where('status', true)
            ->get(['id', 'attribute_id'])
            ->groupBy('attribute_id')
            ->map(fn ($values) => $values->pluck('id')->map(fn ($id) => (int) $id)->all())
            ->all();
    }

    private function filterableAttributeIds(int $categoryId): array
    {
        $visited = [];
        while ($categoryId && ! in_array($categoryId, $visited, true)) {
            $visited[] = $categoryId;
            $rows = DB::table('category_attribute')
                ->where('category_id', $categoryId)
                ->orderBy('position')
                ->get(['attribute_id', 'is_filterable']);

            if ($rows->isNotEmpty()) {
                return $rows->where('is_filterable', true)
                    ->pluck('attribute_id')->map(fn ($id) => (int) $id)->values()->all();
            }

            $categoryId = (int) Category::whereKey($categoryId)->value('parent_id');
        }

        return [];
    }

    private function availableAttributeFilters(array $categoryIds, array $attributeIds): array
    {
        if ($attributeIds === []) {
            return [];
        }

        sort($categoryIds);
        sort($attributeIds);
        $scope = sha1(implode(',', $categoryIds).'|'.implode(',', $attributeIds));
        $version = ShopFilterCache::version();

        return Cache::remember("api.shop-filter.attributes.{$scope}.v{$version}", now()->addMinutes(10), function () use ($categoryIds, $attributeIds) {
            $rows = DB::table('attributes as attribute')
                ->join('attribute_values as value', 'value.attribute_id', '=', 'attribute.id')
                ->join('attribute_value_product_variant as pivot', 'pivot.attribute_value_id', '=', 'value.id')
                ->join('product_variants as variant', 'variant.id', '=', 'pivot.product_variant_id')
                ->join('products as product', 'product.id', '=', 'variant.product_id')
                ->whereIn('attribute.id', $attributeIds)
                ->whereIn('product.category_id', $categoryIds)
                ->where('attribute.status', true)
                ->where('value.status', true)
                ->where('variant.status', true)
                ->where('product.status', true)
                ->groupBy('attribute.id', 'attribute.name', 'attribute.slug', 'attribute.type', 'attribute.position', 'value.id', 'value.value', 'value.color_code', 'value.position')
                ->orderBy('attribute.position')->orderBy('attribute.name')
                ->orderBy('value.position')->orderBy('value.value')
                ->get([
                    'attribute.id as attribute_id', 'attribute.name as attribute_name',
                    'attribute.slug as attribute_slug', 'attribute.type as attribute_type',
                    'value.id as value_id', 'value.value', 'value.color_code',
                    DB::raw('COUNT(DISTINCT product.id) as product_count'),
                ]);

            return $rows->groupBy('attribute_id')->map(fn ($values) => [
                'id' => (int) $values->first()->attribute_id,
                'name' => $values->first()->attribute_name,
                'slug' => $values->first()->attribute_slug,
                'type' => $values->first()->attribute_type,
                'values' => $values->map(fn ($value) => [
                    'id' => (int) $value->value_id,
                    'value' => $value->value,
                    'color_code' => $value->color_code,
                    'product_count' => (int) $value->product_count,
                ])->values()->all(),
            ])->values()->all();
        });
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
