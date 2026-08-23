<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Coupons';
        $search = trim((string) $request->query('search', ''));
        $getCoupons = Coupon::query()
            ->with('createdBy:id,name,type')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) =>
                $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")
            ))
            ->latest('id')
            ->paginate($this->perPage($request))
            ->withQueryString();

        $products = Product::query()->where('status', true)->orderBy('product_name')->get(['id', 'product_name']);
        $categoryRecords = Category::query()
            ->with('section:id,name')
            ->where('status', true)
            ->orderBy('section_id')
            ->orderBy('position')
            ->orderBy('category_name')
            ->get(['id', 'parent_id', 'section_id', 'category_name', 'position']);
        $categoriesById = $categoryRecords->keyBy('id'); //1 => Electronics, 2 => Mobile, 3 => Android need parent category
        $categories = $categoryRecords->map(function (Category $category) use ($categoriesById) {
            $path = [$category->category_name];
            $parentId = (int) $category->parent_id;
            $depth = 0;

            while ($parentId > 0 && $depth < 10 && ($parent = $categoriesById->get($parentId))) {
                array_unshift($path, $parent->category_name);
                $parentId = (int) $parent->parent_id;
                $depth++;
            }

            return [
                'id' => $category->id,
                'path' => implode(' > ', $path),
                'section' => $category->section?->name ?? 'No Section',
                'depth' => $depth,
            ];
        })->sortBy(fn (array $category) => $category['section'].'|'.$category['path'])->values();
        $brands = Brand::query()->where('status', true)->orderBy('name')->get(['id', 'name']);
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.coupon.coupon', compact('title', 'getCoupons', 'products', 'categories', 'brands', 'users'));
    }

    public function store(Request $request)
    {
        $this->saveCoupon(new Coupon(), $request);
        return response()->json(['message' => 'Coupon created successfully.'], 201);
    }

    public function show(Coupon $coupon)
    {
        $coupon->load(['products:id', 'categories:id', 'brands:id', 'users:id']);

        return response()->json(['record' => [
            ...$coupon->toArray(),
            'is_active' => $coupon->is_active ? '1' : '0',
            'exclude_discounted_products' => $coupon->exclude_discounted_products ? '1' : '0',
            'starts_at' => $coupon->starts_at?->format('Y-m-d\TH:i'),
            'expires_at' => $coupon->expires_at?->format('Y-m-d\TH:i'),
            'product_ids' => $coupon->products->pluck('id')->map(fn ($id) => (string) $id)->all(),
            'category_ids' => $coupon->categories->pluck('id')->map(fn ($id) => (string) $id)->all(),
            'brand_ids' => $coupon->brands->pluck('id')->map(fn ($id) => (string) $id)->all(),
            'user_ids' => $coupon->users->pluck('id')->map(fn ($id) => (string) $id)->all(),
        ]]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $this->saveCoupon($coupon, $request);
        return response()->json(['message' => 'Coupon updated successfully.']);
    }

    public function updateStatus(Coupon $coupon)
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);
        return response()->json(['message' => 'Coupon status updated successfully.']);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return response()->json(['message' => 'Coupon deleted successfully.']);
    }

    private function saveCoupon(Coupon $coupon, Request $request): void
    {
        $data = $this->validatedData($request, $coupon->exists ? $coupon : null);
        $relations = [
            'products' => $data['product_ids'] ?? [],
            'categories' => $data['category_ids'] ?? [],
            'brands' => $data['brand_ids'] ?? [],
            'users' => $data['user_ids'] ?? [],
        ];
        unset($data['product_ids'], $data['category_ids'], $data['brand_ids'], $data['user_ids']);

        $data['minimum_lifetime_spend'] = $data['customer_scope'] === 'lifetime_spend'
            ? $data['minimum_lifetime_spend']
            : null;

        $data['code'] = strtoupper(trim($data['code']));
        $data['maximum_discount'] = $data['discount_type'] === 'percentage' ? ($data['maximum_discount'] ?? null) : null;
        if ($data['discount_type'] === 'free_shipping') $data['discount_value'] = 0;

        DB::transaction(function () use ($coupon, $data, $relations) {
            $coupon->fill($data);
            if (! $coupon->exists) $coupon->created_by_id = Auth::guard('admin')->id();
            $coupon->save();

            $coupon->products()->sync($coupon->scope === 'products' ? $relations['products'] : []);
            $coupon->categories()->sync($coupon->scope === 'categories' ? $relations['categories'] : []);
            $coupon->brands()->sync($coupon->scope === 'brands' ? $relations['brands'] : []);
            $coupon->users()->sync($coupon->customer_scope === 'selected' ? $relations['users'] : []);
        });
    }

    private function validatedData(Request $request, ?Coupon $coupon = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'code' => ['required', 'string', 'max:100', Rule::unique('coupons', 'code')->ignore($coupon)],
            'apply_method' => ['required', Rule::in(['code'])],
            'scope' => ['required', Rule::in(['all', 'products', 'categories', 'brands'])],
            'customer_scope' => ['required', Rule::in(['all', 'selected', 'first_order', 'lifetime_spend'])],
            'discount_type' => ['required', Rule::in(['fixed', 'percentage', 'free_shipping'])],
            'discount_value' => ['required', 'numeric', 'min:0', Rule::when($request->discount_type === 'percentage', ['max:100'])],
            'maximum_discount' => ['nullable', 'numeric', 'min:0'],
            'minimum_order_amount' => ['required', 'numeric', 'min:0'],
            'minimum_lifetime_spend' => [Rule::requiredIf($request->customer_scope === 'lifetime_spend'), 'nullable', 'numeric', 'min:0.01'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'exclude_discounted_products' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', Rule::when($request->filled('starts_at'), ['after:starts_at'])],
            'is_active' => ['required', 'boolean'],
            'product_ids' => [Rule::requiredIf($request->scope === 'products'), 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'category_ids' => [Rule::requiredIf($request->scope === 'categories'), 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'brand_ids' => [Rule::requiredIf($request->scope === 'brands'), 'array'],
            'brand_ids.*' => ['integer', 'exists:brands,id'],
            'user_ids' => [Rule::requiredIf($request->customer_scope === 'selected'), 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);
    }
}
