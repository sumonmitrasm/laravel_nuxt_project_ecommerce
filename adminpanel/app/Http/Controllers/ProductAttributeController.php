<?php

namespace App\Http\Controllers;

use App\Models\ProductAttributeDefinition;
use App\Models\ProductAttributeValue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductAttributeController extends Controller
{
    public function index()
    {
        $title = 'Product Attributes';
        $attributes = ProductAttributeDefinition::with(['values' => fn ($query) => $query->orderBy('position')->orderBy('value')])
            ->orderBy('position')->orderBy('name')->get();

        return view('admin.product-attribute.index', compact('title', 'attributes'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:attributes,name'],
            'type' => ['required', Rule::in(['text', 'color'])],
        ]);
        $data['slug'] = Str::slug($data['name']);
        $data['position'] = (int) ProductAttributeDefinition::max('position') + 1;
        ProductAttributeDefinition::create($data);

        return $this->respond($request, 'Attribute created successfully.');
    }

    public function storeValue(Request $request, ProductAttributeDefinition $attribute): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'value' => ['required', 'string', 'max:100', Rule::unique('attribute_values')->where('attribute_id', $attribute->id)],
            'color_code' => ['nullable', 'string', 'max:20'],
        ]);
        $data['position'] = (int) $attribute->values()->max('position') + 1;
        $attribute->values()->create($data);

        return $this->respond($request, 'Attribute value created successfully.');
    }

    public function destroy(Request $request, ProductAttributeDefinition $attribute): RedirectResponse|JsonResponse
    {
        if ($attribute->values()->whereHas('variants')->exists()) {
            if ($request->expectsJson()) return response()->json(['message' => 'This attribute is used by product variants and cannot be deleted.'], 422);
            return back()->withErrors(['attribute' => 'This attribute is used by product variants and cannot be deleted.']);
        }
        $attribute->delete();
        return $this->respond($request, 'Attribute deleted successfully.');
    }

    public function destroyValue(Request $request, ProductAttributeValue $value): RedirectResponse|JsonResponse
    {
        if ($value->variants()->exists()) {
            if ($request->expectsJson()) return response()->json(['message' => 'This value is used by product variants and cannot be deleted.'], 422);
            return back()->withErrors(['attribute' => 'This value is used by product variants and cannot be deleted.']);
        }
        $value->delete();
        return $this->respond($request, 'Attribute value deleted successfully.');
    }

    private function respond(Request $request, string $message): RedirectResponse|JsonResponse
    {
        return $request->expectsJson()
            ? response()->json(['message' => $message])
            : back()->with('success', $message);
    }
}
