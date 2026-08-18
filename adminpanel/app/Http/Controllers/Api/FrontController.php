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
        $priceRange = $this->selectedPriceRange($request);
        $sort = $this->selectedSort($request);

        $products = Product::with([
                'brand:id,name',
                'category:id,category_name,category_discount',
                'variants' => fn ($query) => $query->where('status', true)->select('id', 'product_id', 'price'),
            ])
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
            ->when($priceRange !== [], fn ($query) => $this->applyPriceFilter($query, $priceRange))
            ->tap(fn ($query) => $this->applySorting($query, $sort))
            ->paginate(8)
            ->withQueryString();
        $this->prepareListingPrices($products);

        return response()->json([
            'status' => true,
            'categoryDetails' => $categoryDetails['categoryDetails'],
            'breadcrumbs' => $categoryDetails['breadcrumbs'],
            'filters' => [
                'brands' => $this->availableBrands($categoryDetails['catIds']),
                'attributes' => $this->availableAttributeFilters($categoryDetails['catIds'], $filterableAttributeIds),
                'price' => $this->availablePriceRange($categoryDetails['catIds']),
            ],
            'products' => $products->items(),
            'pagination' => $this->paginationData($products),
        ], 200);
    }

    public function products(Request $request): JsonResponse
    {
        $brandIds = $this->selectedBrandIds($request);
        $priceRange = $this->selectedPriceRange($request);
        $sort = $this->selectedSort($request);

        $products = Product::with([
                'brand:id,name',
                'category:id,category_name,category_discount',
                'variants' => fn ($query) => $query->where('status', true)->select('id', 'product_id', 'price'),
            ])
            ->where('status', true)
            ->when($brandIds !== [], fn ($query) => $query->whereIn('brand_id', $brandIds))
            ->when($priceRange !== [], fn ($query) => $this->applyPriceFilter($query, $priceRange))
            ->tap(fn ($query) => $this->applySorting($query, $sort))
            ->paginate(8)
            ->withQueryString();
        $this->prepareListingPrices($products);

        return response()->json([
            'status' => true,
            'categoryDetails' => null,
            'breadcrumbs' => [],
            'filters' => [
                'brands' => $this->availableBrands(),
                'attributes' => [],
                'price' => $this->availablePriceRange(),
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

    private function selectedPriceRange(Request $request): array
    {
        $minimum = filter_var($request->query('min_price'), FILTER_VALIDATE_FLOAT);
        $maximum = filter_var($request->query('max_price'), FILTER_VALIDATE_FLOAT);
        $minimum = $minimum !== false && $minimum >= 0 ? min((float) $minimum, 999999999999.99) : null;
        $maximum = $maximum !== false && $maximum >= 0 ? min((float) $maximum, 999999999999.99) : null;

        if ($minimum === null && $maximum === null) {
            return [];
        }

        if ($minimum !== null && $maximum !== null && $minimum > $maximum) {
            [$minimum, $maximum] = [$maximum, $minimum];
        }

        return ['min' => $minimum, 'max' => $maximum];
    }

    private function selectedSort(Request $request): string
    {
        $sort = (string) $request->query('sort', 'popular');

        return in_array($sort, ['popular', 'newest', 'price_asc', 'price_desc'], true)
            ? $sort
            : 'popular';
    }

    private function applySorting($query, string $sort): void
    {
        if ($sort === 'newest') {
            $query->latest('products.id');
            return;
        }

        if (in_array($sort, ['price_asc', 'price_desc'], true)) {
            $discount = 'COALESCE(NULLIF(products.product_discount, 0), (SELECT category_discount FROM categories WHERE categories.id = products.category_id), 0)';
            $regularPrice = 'COALESCE((SELECT MIN(COALESCE(product_variants.price, products.product_price)) FROM product_variants WHERE product_variants.product_id = products.id AND product_variants.status = 1), products.product_price)';
            $effectivePrice = "GREATEST(0, ({$regularPrice}) * (1 - ({$discount} / 100)))";
            $direction = $sort === 'price_asc' ? 'asc' : 'desc';

            $query->orderByRaw("{$effectivePrice} {$direction}")
                ->orderByDesc('products.id');
            return;
        }

        // Until real sales/view metrics exist, featured products are the safest
        // popularity signal; newest products break ties deterministically.
        $query->orderByRaw("CASE WHEN products.is_featured = 'Yes' THEN 0 ELSE 1 END")
            ->orderByDesc('products.id');
    }

    private function applyPriceFilter($query, array $range): void
    {
        $discount = 'COALESCE(NULLIF(products.product_discount, 0), (SELECT category_discount FROM categories WHERE categories.id = products.category_id), 0)';
        $basePrice = "GREATEST(0, products.product_price * (1 - ({$discount} / 100)))";
        $variantPrice = "GREATEST(0, COALESCE(product_variants.price, products.product_price) * (1 - ({$discount} / 100)))";

        $query->where(function ($priceQuery) use ($range, $basePrice, $variantPrice) {
            $priceQuery->where(function ($baseQuery) use ($range, $basePrice) {
                $baseQuery->whereDoesntHave('variants', fn ($variant) => $variant->where('status', true));
                $this->applyPriceBounds($baseQuery, $basePrice, $range);
            })->orWhereHas('variants', function ($variant) use ($range, $variantPrice) {
                $variant->where('status', true);
                $this->applyPriceBounds($variant, $variantPrice, $range);
            });
        });
    }

    private function applyPriceBounds($query, string $priceExpression, array $range): void
    {
        if ($range['min'] !== null) {
            $query->whereRaw("{$priceExpression} >= ?", [$range['min']]);
        }
        if ($range['max'] !== null) {
            $query->whereRaw("{$priceExpression} <= ?", [$range['max']]);
        }
    }

    private function availablePriceRange(array $categoryIds = []): array
    {
        sort($categoryIds);
        $scope = $categoryIds === [] ? 'all' : sha1(implode(',', $categoryIds));
        $version = ShopFilterCache::version();

        return Cache::remember("api.shop-filter.price.{$scope}.v{$version}", now()->addMinutes(10), function () use ($categoryIds) {
            $discount = 'COALESCE(NULLIF(product.product_discount, 0), category.category_discount, 0)';
            $effectivePrice = "GREATEST(0, COALESCE(variant.price, product.product_price) * (1 - ({$discount} / 100)))";

            $range = DB::table('products as product')
                ->join('categories as category', 'category.id', '=', 'product.category_id')
                ->leftJoin('product_variants as variant', function ($join) {
                    $join->on('variant.product_id', '=', 'product.id')->where('variant.status', true);
                })
                ->where('product.status', true)
                ->when($categoryIds !== [], fn ($query) => $query->whereIn('product.category_id', $categoryIds))
                ->selectRaw("MIN({$effectivePrice}) as minimum, MAX({$effectivePrice}) as maximum")
                ->first();

            $minimum = $range?->minimum !== null ? floor((float) $range->minimum) : 0;
            $maximum = $range?->maximum !== null ? ceil((float) $range->maximum) : 0;
            $step = max(1, 10 ** max(0, strlen((string) max(1, (int) $maximum)) - 3));

            return ['min' => $minimum, 'max' => $maximum, 'step' => $step];
        });
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

    private function prepareListingPrices(LengthAwarePaginator $products): void
    {
        $products->getCollection()->each(function (Product $product) {
            $regularPrice = $product->variants->isNotEmpty()
                ? $product->variants->map(fn ($variant) => (float) $product->regularPriceForVariant($variant))->min()
                : (float) $product->product_price;

            $product->setAttribute('listing_regular_price', number_format($regularPrice, 2, '.', ''));
            $product->setAttribute('listing_final_price', $product->discountedPrice($regularPrice));
            $product->setAttribute('has_variant_pricing', $product->variants->isNotEmpty());
            $product->unsetRelation('variants');
        });
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
