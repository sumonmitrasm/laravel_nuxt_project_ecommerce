<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductAttributeDefinition;
use App\Models\Section;
use App\Support\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function __construct(private readonly ImageOptimizer $images) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = 'Products';
        $search = trim((string) $request->query('search', ''));
        $getProducts = Product::query()
            ->with(['section:id,name', 'category:id,category_name,category_discount', 'brand:id,name'])
            ->select('id', 'section_id', 'category_id', 'brand_id', 'product_name', 'product_code', 'product_image', 'product_price', 'product_discount', 'is_featured', 'status')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($category) => $category->where('category_name', 'like', "%{$search}%"))
                    ->orWhereHas('brand', fn ($brand) => $brand->where('name', 'like', "%{$search}%"));
            }))
            ->latest('id')
            ->cursorPaginate($this->perPage($request))
            ->withQueryString()
            ->through(fn (Product $product) => [
                ...$product->only(['id', 'product_name', 'product_code', 'product_image', 'product_price', 'product_discount', 'is_featured', 'status']),
                'section_name' => $product->section?->name,
                'category_name' => $product->category?->category_name,
                'brand_name' => $product->brand?->name,
                'category_discount' => $product->category?->category_discount,
                'effective_discount' => $product->effective_discount,
                'final_price' => $product->final_price,
            ]);

        $sections = Section::where('status', true)->orderBy('name')->get(['id', 'name']);
        $categories = Category::where('status', true)->orderBy('category_name')->get(['id', 'parent_id', 'section_id', 'category_name']);
        $categoryGroups = $this->categoryGroups($sections, $categories);
        $brands = Brand::where('status', true)->orderBy('name')->get(['id', 'name']);
        $variantAttributes = ProductAttributeDefinition::query()
            ->where('status', true)
            ->with(['values' => fn ($query) => $query->where('status', true)->orderBy('position')->orderBy('value')])
            ->orderBy('position')->orderBy('name')->get();
        $categoryAttributeMap = $this->categoryAttributeMap($categories);
        return view('admin.product.product', compact('getProducts', 'sections', 'categoryGroups', 'brands', 'variantAttributes', 'categoryAttributeMap', 'title'));
    }

    public function store(Request $request)
    {
        $this->validateVariantSelections($request);
        $data = $this->validatedData($request);
        $admin = Auth::guard('admin')->user();
        $data['admin_id'] = $admin?->id;
        $data['admin_type'] = $admin?->type;
        DB::transaction(function () use ($data, $request) {
            $product = Product::create($data);
            $this->storeProductImages($request, $product);
            $this->syncProductAttributes($request, $product);
        });
        $this->clearMenuCache();
        return response()->json(['message' => 'Product created successfully.'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load([
            'images:id,product_id,image,status',
            'variants:id,product_id,sku,price,stock,status',
            'variants.values:id,attribute_id,value,color_code',
        ]);

        return response()->json([
            'record' => $product,
            'image_urls' => [
                'product_image' => $this->imageUrl($product->product_image),
                'meta_image' => $this->imageUrl($product->meta_image),
            ],
            'product_images' => $product->images->map(fn (ProductImage $image) => [
                'id' => $image->id,
                'url' => asset('admin/productgallery/'.basename($image->image)),
                'status' => $image->status,
                'delete_url' => route('admin-product.image.delete', $image),
            ]),
            'product_variants' => $product->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'stock' => $variant->stock,
                'status' => $variant->status,
                'values' => $variant->values->mapWithKeys(
                    fn ($value) => [(string) $value->attribute_id => $value->id]
                ),
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $this->validateVariantSelections($request, $product);
        $data = $this->validatedData($request, $product);
        DB::transaction(function () use ($data, $request, $product) {
            $product->update($data);
            $this->storeProductImages($request, $product);
            $this->syncProductAttributes($request, $product);
        });
        $this->clearMenuCache();

        return response()->json(['message' => 'Product updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->load('images:id,product_id,image');
        $this->deleteImage($product->product_image);
        $this->deleteImage($product->meta_image);
        foreach ($product->images as $image) {
            $this->images->delete($image->image, 'admin/productgallery');
        }
        $product->delete();
        $this->clearMenuCache();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    public function updateStatus(Product $product)
    {
        $product->update(['status' => ! $product->status]);
        $this->clearMenuCache();

        return response()->json(['message' => 'Product status updated successfully.']);
    }

    public function destroyImage(ProductImage $productImage)
    {
        $this->images->delete($productImage->image, 'admin/productgallery');
        $productImage->delete();

        return response()->json(['message' => 'Product image deleted successfully.']);
    }

    private function validatedData(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'section_id' => ['required', Rule::exists('sections', 'id')->where('status', 1)],
            'category_id' => ['required', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('section_id', $request->input('section_id'))->where('status', 1))],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('status', 1)],
            'product_name' => ['required', 'string', 'max:255'],
            'product_code' => ['required', 'string', 'max:100', Rule::unique('products', 'product_code')->ignore($product)],
            'product_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'product_discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'product_weight' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'product_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:8192'],
            'product_images' => ['nullable', 'array', 'max:10'],
            'product_images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'variants' => ['nullable', 'array', 'max:100'],
            'variants.*.values' => ['nullable', 'array'],
            'variants.*.values.*' => ['nullable', 'integer', Rule::exists('attribute_values', 'id')->where('status', 1)],
            'variants.*.price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.status' => ['nullable', 'boolean'],
            'product_video' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:8192'],
            'url_structure' => ['nullable', 'string', 'max:255'],
            'heading_tag' => ['nullable', 'string', 'max:255'],
            'schema_markup' => ['nullable', 'string'],
            'meta_data' => ['nullable', 'string'],
            'meta_robot' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'canonical_tag' => ['nullable', 'url', 'max:255'],
            'is_featured' => ['required', Rule::in(['No', 'Yes'])],
            'status' => ['required', 'boolean'],
        ], [
            'category_id.exists' => 'Select a category belonging to the selected section.',
            'product_code.unique' => 'This product code already exists.',
        ]);

        foreach (['product_image', 'meta_image'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $this->storeImage($request->file($field), $field);
                if ($product) {
                    $this->deleteImage($product->{$field});
                }
            }
        }

        $data['product_discount'] ??= 0;
        unset($data['product_images'], $data['variants']);

        return $data;
    }

    private function storeProductImages(Request $request, Product $product): void
    {
        foreach ($request->file('product_images', []) as $file) {
            $product->images()->create([
                'image' => $this->images->store(
                    $file,
                    'admin/productgallery',
                    'gallery',
                    1600,
                    1600,
                    84,
                ),
                'status' => true,
            ]);
        }
    }

    private function syncProductAttributes(Request $request, Product $product): void
    {
        $variants = collect($request->input('variants', []))
            ->filter(fn (array $row) => collect($row)->except(['status', 'values'])->contains(
                fn ($value) => $value !== null && $value !== ''
            ) || collect($row['values'] ?? [])->contains(fn ($value) => $value !== null && $value !== ''));

        $product->variants()->delete();
        foreach ($variants as $row) {
            $variant = $product->variants()->create([
                'sku' => $row['sku'] ?: $product->product_code.'-'.str()->upper(str()->random(6)),
                'price' => ($row['price'] ?? '') !== '' ? $row['price'] : null,
                'stock' => ($row['stock'] ?? '') !== '' ? $row['stock'] : 0,
                'status' => (bool) ($row['status'] ?? true),
            ]);
            $variant->values()->sync(collect($row['values'] ?? [])->filter()->unique()->values());
        }
    }

    private function validateVariantSelections(Request $request, ?Product $product = null): void
    {
        $rows = collect($request->input('variants', []));
        $errors = [];
        $combinations = [];
        $skus = [];
        $applicable = collect($this->applicableCategoryAttributes((int) $request->input('category_id')));
        $allowedAttributeIds = $applicable->keys()->map(fn ($id) => (int) $id);
        $requiredAttributeIds = $applicable->filter(fn ($config) => $config['is_required'])->keys()->map(fn ($id) => (int) $id);

        foreach ($rows as $index => $row) {
            $values = collect($row['values'] ?? [])->filter()->map(fn ($id) => (int) $id);
            if ($values->isEmpty() && empty($row['sku']) && ($row['price'] ?? '') === '' && ($row['stock'] ?? '') === '') continue;

            foreach ($values->keys() as $attributeId) {
                if (! $allowedAttributeIds->contains((int) $attributeId)) {
                    $errors["variants.$index.values.$attributeId"] = 'This attribute is not available for the selected category.';
                }
            }
            foreach ($requiredAttributeIds as $attributeId) {
                if (! $values->has($attributeId) && ! $values->has((string) $attributeId)) {
                    $errors["variants.$index.values.$attributeId"] = 'This category requires an option for this attribute.';
                }
            }

            $actual = DB::table('attribute_values')->whereIn('id', $values->values())->pluck('attribute_id', 'id');
            foreach ($values as $attributeId => $valueId) {
                if ((int) ($actual[$valueId] ?? 0) !== (int) $attributeId) {
                    $errors["variants.$index.values.$attributeId"] = 'The selected value does not belong to this attribute.';
                }
            }

            $combination = $values->sort()->implode('-');
            if ($combination !== '' && isset($combinations[$combination])) {
                $errors["variants.$index.values"] = 'This option combination is duplicated.';
            }
            $combinations[$combination] = true;

            $sku = trim((string) ($row['sku'] ?? ''));
            if ($sku !== '') {
                if (isset($skus[$sku])) $errors["variants.$index.sku"] = 'Variant SKU must be unique.';
                $skus[$sku] = true;
                $exists = DB::table('product_variants')->where('sku', $sku)
                    ->when($product, fn ($query) => $query->where('product_id', '!=', $product->id))->exists();
                if ($exists) $errors["variants.$index.sku"] = 'This variant SKU is already in use.';
            }
        }

        if ($errors) throw ValidationException::withMessages($errors);
    }

    private function categoryAttributeMap($categories): array
    {
        $map = [];
        foreach ($categories as $category) {
            $map[(string) $category->id] = $this->applicableCategoryAttributes((int) $category->id);
        }
        return $map;
    }

    private function applicableCategoryAttributes(int $categoryId): array
    {
        $visited = [];
        while ($categoryId && ! in_array($categoryId, $visited, true)) {
            $visited[] = $categoryId;
            $rows = DB::table('category_attribute')
                ->where('category_id', $categoryId)
                ->orderBy('position')
                ->get(['attribute_id', 'is_filterable', 'is_required', 'position']);
            if ($rows->isNotEmpty()) {
                return $rows->mapWithKeys(fn ($row) => [(string) $row->attribute_id => [
                    'is_filterable' => (bool) $row->is_filterable,
                    'is_required' => (bool) $row->is_required,
                    'position' => (int) $row->position,
                ]])->all();
            }
            $categoryId = (int) Category::whereKey($categoryId)->value('parent_id');
        }
        return [];
    }

    private function storeImage($file, string $prefix): string
    {
        [$width, $height] = $prefix === 'meta_image' ? [1200, 630] : [1600, 1600];

        return $this->images->store($file, 'admin/productimage', $prefix, $width, $height, 84);
    }

    private function deleteImage(?string $image): void
    {
        $this->images->delete($image, 'admin/productimage');
    }

    private function imageUrl(?string $image): ?string
    {
        return $image ? asset('admin/productimage/'.basename($image)) : null;
    }

    private function categoryGroups($sections, $categories): array
    {
        $groups = [];

        foreach ($sections as $section) {
            $sectionCategories = $categories->where('section_id', $section->id)->values();
            $categoryIds = $sectionCategories->pluck('id')->map(fn ($id) => (int) $id)->all();
            $childrenByParent = $sectionCategories->groupBy(fn (Category $category) => (int) $category->parent_id);
            $roots = $sectionCategories->filter(fn (Category $category) =>
                (int) $category->parent_id === 0 || ! in_array((int) $category->parent_id, $categoryIds, true)
            );

            foreach ($roots as $root) {
                $options = [];
                $visited = [(int) $root->id => true];
                $this->appendCategoryChildren($root->id, $childrenByParent, $options, 1, $visited);
                $groups[] = [
                    'section_id' => (int) $section->id,
                    'section_name' => $section->name,
                    'root_id' => (int) $root->id,
                    'root_name' => $root->category_name,
                    'options' => $options,
                ];
            }
        }

        return $groups;
    }

    private function appendCategoryChildren(int $parentId, $childrenByParent, array &$options, int $depth, array &$visited): void
    {
        foreach ($childrenByParent->get($parentId, collect()) as $category) {
            if (isset($visited[(int) $category->id])) {
                continue;
            }
            $visited[(int) $category->id] = true;
            $options[] = [
                'id' => (int) $category->id,
                'name' => $category->category_name,
                'depth' => $depth,
            ];
            $this->appendCategoryChildren($category->id, $childrenByParent, $options, $depth + 1, $visited);
        }
    }

    private function clearMenuCache(): void
    {
        Cache::forget('api.sections-with-categories.v4');
    }

}
