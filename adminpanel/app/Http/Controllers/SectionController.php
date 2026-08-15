<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Support\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    public function __construct(private readonly ImageOptimizer $images) {}

    public function section(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $sections = Section::select('id', 'name', 'image', 'status')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest('id')
            ->cursorPaginate($this->perPage($request))
            ->withQueryString()
            ->through(fn (Section $section) => $section->only(['id', 'name', 'image', 'status']));
        $title = "Section Page";
        return view('admin.sections.section', compact('sections', 'title'));
    }

    public function store(Request $request)
    {
        Section::create($this->validateSection($request));
        $this->clearSectionCache();
        return response()->json(['message' => 'Section saved successfully.'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Section $section)
    {
        return response()->json([
            'record' => $section,
            'image_url' => $section->image ? asset('admin/sectionimage/'.basename($section->image)) : null,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Section $section)
    {
        return response()->json(['record' => $section]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Section $section)
    {
        $section->update($this->validateSection($request, $section));
        $this->clearSectionCache();
        return response()->json(['message' => 'Section updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Section $section)
    {
        $this->deleteOldImage($section->image);
        $section->delete();
        $this->clearSectionCache();
        return response()->json(['message' => 'Section deleted successfully.']);
    }

    public function updateStatus(Section $section)
    {
        $section->update(['status' => ! $section->status]);
        $this->clearSectionCache();
        return response()->json(['message' => 'Section status updated successfully.']);
    }

    private function validateSection(Request $request, ?Section $section = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sections', 'name')->ignore($section)],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:8192'],
            'status' => ['required', 'boolean'],
        ], [
            'name.unique' => 'This section name already exists.',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request->file('image'));
            if ($section) {
                $this->deleteOldImage($section->image);
            }
        } else {
            unset($data['image']);
        }

        return $data;
    }

    private function uploadImage($file): string
    {
        return $this->images->store($file, 'admin/sectionimage', 'section', 1200, 1200, 84);
    }

    private function deleteOldImage(?string $imageName): void
    {
        $this->images->delete($imageName, 'admin/sectionimage');
    }

    private function clearSectionCache(): void
    {
        Cache::forget('api.sections-with-categories.v1');
        Cache::forget('api.sections-with-categories.v2');
        Cache::forget('api.sections-with-categories.v3');
        Cache::forget('api.sections-with-categories.v4');
    }

}
