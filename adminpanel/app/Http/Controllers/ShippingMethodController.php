<?php

namespace App\Http\Controllers;

use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class ShippingMethodController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Shipping Methods';
        $search = trim((string) $request->query('search', ''));
        $shippingMethods = ShippingMethod::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")))
            ->orderBy('position')->orderBy('id')
            ->cursorPaginate($this->perPage($request))->withQueryString();

        return view('admin.shipping-method.index', compact('title', 'shippingMethods'));
    }

    public function store(Request $request)
    {
        ShippingMethod::create($this->validated($request));
        $this->clearCache();
        return response()->json(['message' => 'Shipping method created successfully.'], 201);
    }

    public function show(ShippingMethod $shippingMethod)
    {
        return response()->json(['record' => $shippingMethod]);
    }

    public function update(Request $request, ShippingMethod $shippingMethod)
    {
        $shippingMethod->update($this->validated($request, $shippingMethod));
        $this->clearCache();
        return response()->json(['message' => 'Shipping method updated successfully.']);
    }

    public function updateStatus(ShippingMethod $shippingMethod)
    {
        $shippingMethod->update(['status' => ! $shippingMethod->status]);
        $this->clearCache();
        return response()->json(['message' => 'Shipping method status updated successfully.']);
    }

    public function destroy(ShippingMethod $shippingMethod)
    {
        $shippingMethod->delete();
        $this->clearCache();
        return response()->json(['message' => 'Shipping method deleted successfully.']);
    }

    private function validated(Request $request, ?ShippingMethod $method = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/', Rule::unique('shipping_methods')->ignore($method)],
            'description' => ['nullable', 'string', 'max:255'],
            'charge' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'delivery_time' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9 _-]+$/'],
            'position' => ['required', 'integer', 'min:0', 'max:10000'],
            'status' => ['required', 'boolean'],
        ]);
    }

    private function clearCache(): void
    {
        Cache::forget('api.shipping-methods.v1');
    }
}
