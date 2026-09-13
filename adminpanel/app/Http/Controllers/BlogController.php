<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Tag;
use App\Support\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    public function __construct(private readonly ImageOptimizer $images) {}

    public function index(Request $request)
    {
        $title = 'Blogs';
        $search = trim((string) $request->query('search', ''));

        $blogs = Blog::query()
            ->with(['author:id,name', 'tags:id,name'])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            }))
            ->latest('id')
            ->cursorPaginate($this->perPage($request))
            ->withQueryString();

        $tags = Tag::query()->where('status', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.blog.index', compact('blogs', 'tags', 'title'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);
        $data['author_id'] = Auth::guard('admin')->id();

        $blog = Blog::create($data);
        $blog->tags()->sync($tagIds);

        return response()->json(['message' => 'Blog created successfully.'], 201);
    }

    public function show(Blog $blog)
    {
        $record = $blog->toArray();
        $record['published_at'] = $blog->published_at?->format('Y-m-d\\TH:i');
        $record['tag_ids'] = $blog->tags()->pluck('tags.id')->all();

        return response()->json([
            'record' => $record,
            'image_url' => $blog->image ? asset('admin/blogimage/'.$blog->image) : null,
        ]);
    }

    public function update(Request $request, Blog $blog)
    {
        $data = $this->validatedData($request, $blog);
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $blog->update($data);
        $blog->tags()->sync($tagIds);

        return response()->json(['message' => 'Blog updated successfully.']);
    }

    public function updateStatus(Blog $blog)
    {
        $blog->update(['status' => ! $blog->status]);

        return response()->json(['message' => 'Blog status updated successfully.']);
    }

    public function destroy(Blog $blog)
    {
        $this->images->delete($blog->image, 'admin/blogimage');
        $blog->delete();

        return response()->json(['message' => 'Blog deleted successfully.']);
    }

    private function validatedData(Request $request, ?Blog $blog = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('blogs', 'title')->ignore($blog)],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:10240'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'meta_robot' => ['required', Rule::in(['index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow'])],
            'status' => ['required', 'boolean'],
        ]);

        $data['slug'] = $this->uniqueSlug($data['title'], $blog);

        if ($request->hasFile('image')) {
            $data['image'] = $this->images->store($request->file('image'), 'admin/blogimage', 'blog', 1600, 1000, 84);
            if ($blog) {
                $this->images->delete($blog->image, 'admin/blogimage');
            }
        }

        return $data;
    }

    private function uniqueSlug(string $title, ?Blog $blog = null): string
    {
        $base = Str::slug($title) ?: 'blog';
        $slug = $base;
        $number = 2;

        while (Blog::query()->where('slug', $slug)->when($blog, fn ($query) => $query->whereKeyNot($blog->id))->exists()) {
            $slug = $base.'-'.$number;
            $number++;
        }

        return $slug;
    }
}