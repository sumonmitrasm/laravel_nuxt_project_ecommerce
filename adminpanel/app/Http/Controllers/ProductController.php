<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Section;
use App\Support\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

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
            ->select('id', 'section_id', 'category_id', 'brand_id', 'product_name', 'product_code', 'product_image', 'product_color', 'product_price', 'product_discount', 'is_featured', 'status')
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
                ...$product->only(['id', 'product_name', 'product_code', 'product_image', 'product_color', 'product_price', 'product_discount', 'is_featured', 'status']),
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
        $colors = Color::where('status', true)->orderBy('name')->get(['id', 'name', 'color_code']);
        return view('admin.product.product', compact('getProducts', 'sections', 'categoryGroups', 'brands', 'colors', 'title'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $admin = Auth::guard('admin')->user();
        $data['admin_id'] = $admin?->id;
        $data['admin_type'] = $admin?->type;
        $product = Product::create($data);
        $this->storeProductImages($request, $product);
        $this->clearMenuCache();
        return response()->json(['message' => 'Product created successfully.'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('images:id,product_id,image,status');

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
        $product->update($this->validatedData($request, $product));
        $this->storeProductImages($request, $product);
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
            'product_color' => ['nullable', Rule::exists('colors', 'color_code')->where('status', 1)],
            'product_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'product_discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'product_weight' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'product_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:8192'],
            'product_images' => ['nullable', 'array', 'max:10'],
            'product_images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
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
        unset($data['product_images']);

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
