<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = 'Color Page';
        $search = trim((string) $request->query('search', ''));
        $getColors = Color::select('id','name','color_code','status')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('color_code', 'like', "%{$search}%");
            }))
            ->latest('id')
            ->cursorPaginate($this->perPage($request))
            ->withQueryString()
            ->through(fn (Color $color) => $color->only(['id','name','color_code','status']));
        return view('admin.color.color', compact('getColors', 'title'));
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
        Color::create($this->validatedData($request));

        return response()->json(['message' => 'Color created successfully.'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Color $color)
    {
        return response()->json(['record' => $color]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Color $color)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Color $color)
    {
        $color->update($this->validatedData($request, $color));

        return response()->json(['message' => 'Color updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Color $color)
    {
        $color->delete();

        return response()->json(['message' => 'Color deleted successfully.']);
    }

    public function updateStatus(Color $color)
    {
        $color->update(['status' => ! $color->status]);

        return response()->json(['message' => 'Color status updated successfully.']);
    }

    private function validatedData(Request $request, ?Color $color = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('colors', 'name')->ignore($color)],
            'color_code' => [
                'required',
                'string',
                'max:9',
                'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/',
                Rule::unique('colors', 'color_code')->ignore($color),
            ],
            'status' => ['required', 'boolean'],
        ], [
            'name.unique' => 'This color name already exists.',
            'color_code.regex' => 'Enter a valid HEX color code, for example #FF0000.',
            'color_code.unique' => 'This color code already exists.',
        ]);

        $data['color_code'] = strtoupper($data['color_code']);

        return $data;
    }
}
