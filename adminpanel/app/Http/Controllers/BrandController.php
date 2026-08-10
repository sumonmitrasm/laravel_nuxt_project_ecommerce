<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = 'Brand Page';
        $search = trim((string) $request->query('search', ''));
        $getBrands = Brand::select('id', 'name','status')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            }))
            ->latest('id')
            ->cursorPaginate($this->perPage($request))
            ->withQueryString()
            ->through(fn (Brand $brand) => $brand->only(['id', 'name','status']));
        return view('admin.brand.brand', compact('getBrands', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Brand::create($this->validatedData($request));

        return response()->json(['message' => 'Brand created successfully.'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        return response()->json(['record' => $brand]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $brand->update($this->validatedData($request, $brand));

        return response()->json(['message' => 'Brand updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        $brand->delete();

        return response()->json(['message' => 'Brand deleted successfully.']);
    }

    public function updateStatus(Brand $brand)
    {
        $brand->update(['status' => ! $brand->status]);

        return response()->json(['message' => 'Brand status updated successfully.']);
    }

    private function validatedData(Request $request, ?Brand $brand = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($brand)],
            'status' => ['required', 'boolean'],
        ], [
            'name.unique' => 'This brand name already exists.',
        ]);
    }
}
