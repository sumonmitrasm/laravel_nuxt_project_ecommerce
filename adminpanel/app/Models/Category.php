<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $appends = ['image_url'];

    protected $fillable = [
        'parent_id',
        'section_id',
        'category_name',
        'description',
        'image',
        'position',
        'url',
        'url_structure',
        'heading_tag',
        'schema_markup',
        'meta_title',
        'meta_data',
        'meta_description',
        'meta_keywords',
        'meta_robot',
        'category_discount',
        'status',
    ];
    protected $casts = [
        'status' => 'boolean',
        'parent_id' => 'integer',
        'section_id' => 'integer',
        'position' => 'integer',
        'category_discount' => 'decimal:2',
    ];
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id')->select('id','name');
    }
    public function parentcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id')->withDefault(['category_name' => 'Root / Main Category']);
    }
    public function subcategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->where('status', 1)
            ->with(['subcategories', 'products.brand'])
            ->orderBy('position')
            ->orderBy('category_name');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id')
            ->where('status', true)
            ->with('brand:id,name')
            ->latest('id');
    }

    public static function categories(): array
    {
        return self::with('subcategories')
            ->where('parent_id', 0)
            ->where('status', true)
            ->orderBy('position')
            ->orderBy('category_name')
            ->get()
            ->toArray();
    }

    public static function categoryDetails(string $url): ?array
    {
        $category = self::with('subcategories')
            ->where('url', $url)
            ->where('status', true)
            ->first();

        if (! $category) {
            return null;
        }

        $categoryDetails = $category->toArray();
        $catIds = [$category->id, ...self::descendantIds($categoryDetails['subcategories'] ?? [])];
        $breadcrumbs = [];

        if ($category->parent_id) {
            $parent = self::whereKey($category->parent_id)->first(['id', 'category_name', 'url']);
            if ($parent) {
                $breadcrumbs[] = $parent->only(['id', 'category_name', 'url']);
            }
        }
        $breadcrumbs[] = $category->only(['id', 'category_name', 'url']);

        return [
            'catIds' => array_values(array_unique($catIds)),
            'categoryDetails' => $categoryDetails,
            'breadcrumbs' => $breadcrumbs,
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('admin/categoryimage/'.basename($this->image)) : null;
    }

    private static function descendantIds(array $categories): array
    {
        $ids = [];
        foreach ($categories as $category) {
            $ids[] = $category['id'];
            $ids = [...$ids, ...self::descendantIds($category['subcategories'] ?? [])];
        }

        return $ids;
    }
}
