<?php

namespace App\Http\Controllers;

use App\Models\HomeSlider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class HomeSliderController extends Controller
{
    public function index()
    {
        return view('admin.home-slider.index', ['title' => 'Home Sliders', 'sliders' => HomeSlider::orderBy('position')->orderBy('id')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) $data['image'] = $this->upload($request->file('image'));
        HomeSlider::create($data);
        $this->clearCache();
        return response()->json(['message' => 'Home slider created successfully.'], 201);
    }

    public function show(HomeSlider $homeSlider)
    {
        return response()->json(['record' => $homeSlider, 'image_url' => $homeSlider->image_url]);
    }

    public function update(Request $request, HomeSlider $homeSlider)
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $old = $homeSlider->image;
            $data['image'] = $this->upload($request->file('image'));
            $this->deleteImage($old);
        }
        $homeSlider->update($data);
        $this->clearCache();
        return response()->json(['message' => 'Home slider updated successfully.']);
    }

    public function status(HomeSlider $homeSlider)
    {
        $homeSlider->update(['status' => ! $homeSlider->status]);
        $this->clearCache();
        return response()->json(['message' => 'Slider status updated.']);
    }

    public function destroy(HomeSlider $homeSlider)
    {
        $image = $homeSlider->image;
        $homeSlider->delete();
        $this->deleteImage($image);
        $this->clearCache();
        return response()->json(['message' => 'Home slider deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'eyebrow' => ['nullable','string','max:100'], 'title' => ['required','string','max:150'],
            'description' => ['nullable','string','max:500'], 'offer_label' => ['nullable','string','max:50'],
            'offer_text' => ['nullable','string','max:50'], 'offer_note' => ['nullable','string','max:100'],
            'button_text' => ['nullable','string','max:50'], 'button_url' => ['nullable','string','max:500'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'background_color' => ['required','regex:/^#[0-9A-Fa-f]{6}$/'],
            'position' => ['required','integer','min:0','max:10000'], 'status' => ['required','boolean'],
        ]);
    }

    private function upload($file): string
    {
        $directory = public_path('admin/home_sliders');
        if (! is_dir($directory)) mkdir($directory, 0777, true);
        $name = time().'_'.Str::random(10).'.webp';
        $image = ImageManager::usingDriver(GdDriver::class)->decodePath($file->getRealPath());
        $image->scaleDown(width: 1200, height: 700)->encode(new WebpEncoder(quality: 78))->save($directory.'/'.$name);
        return $name;
    }

    private function deleteImage(?string $name): void
    {
        if ($name && is_file($path = public_path('admin/home_sliders/'.basename($name)))) @unlink($path);
    }

    private function clearCache(): void { Cache::forget('api.home-sliders.v1'); }
}
