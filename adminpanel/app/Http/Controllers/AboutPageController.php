<?php

namespace App\Http\Controllers;

use App\Models\AboutPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AboutPageController extends Controller
{
    public function edit(): View
    {
        $about = AboutPage::query()->firstOrCreate([], ['return_days' => 7]);

        return view('admin.about.edit', compact('about'));
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_highlight' => ['nullable', 'string', 'max:255'],
            'hero_text' => ['nullable', 'string', 'max:1000'],
            'intro_title' => ['nullable', 'string', 'max:255'],
            'intro_text' => ['nullable', 'string', 'max:2000'],
            'promise_title' => ['nullable', 'string', 'max:255'],
            'promise_text' => ['nullable', 'string', 'max:2000'],
            'cta_title' => ['nullable', 'string', 'max:255'],
            'return_days' => ['required', 'integer', 'min:1', 'max:365'],
            'value_1_title' => ['nullable', 'string', 'max:100'],
            'value_1_text' => ['nullable', 'string', 'max:500'],
            'value_2_title' => ['nullable', 'string', 'max:100'],
            'value_2_text' => ['nullable', 'string', 'max:500'],
            'value_3_title' => ['nullable', 'string', 'max:100'],
            'value_3_text' => ['nullable', 'string', 'max:500'],
            'value_4_title' => ['nullable', 'string', 'max:100'],
            'value_4_text' => ['nullable', 'string', 'max:500'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        AboutPage::query()->firstOrCreate([])->update($data);
        Cache::forget('api.about.v1');

        if ($request->expectsJson()) {
            return response()->json(['message' => 'About page updated successfully.']);
        }

        return back()->with('success', 'About page updated successfully.');
    }
}