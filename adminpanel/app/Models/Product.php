<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $appends = ['image_url', 'effective_discount', 'final_price'];

    protected $fillable = [
        'section_id', 'category_id', 'brand_id', 'admin_id', 'vendor_id', 'admin_type',
        'product_name', 'product_code', 'product_color', 'product_price', 'product_discount',
        'product_weight', 'product_image', 'product_video', 'description', 'meta_title',
        'meta_description', 'meta_image', 'url_structure', 'heading_tag', 'schema_markup',
        'meta_data', 'meta_robot', 'meta_keywords', 'canonical_tag', 'is_featured', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'product_price' => 'decimal:2',
            'product_discount' => 'decimal:2',
            'product_weight' => 'decimal:2',
        ];
    }

    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }
    public function admin(): BelongsTo { return $this->belongsTo(Admin::class); }

    public static function getDiscountPrice(int $productId): float
    {
        $product = self::with('category:id,category_discount')
            ->whereKey($productId)
            ->first(['id', 'product_price', 'product_discount', 'category_id']);

        if (! $product) {
            return 0;
        }

        $discount = $product->effective_discount;

        return $discount > 0
            ? round((float) $product->product_price * (1 - $discount / 100), 2)
            : (float) $product->product_price;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->product_image
            ? asset('admin/productimage/'.basename($this->product_image))
            : null;
    }

    public function getFinalPriceAttribute(): string
    {
        $price = (float) $this->product_price;
        $discount = $this->effective_discount;

        return number_format(max(0, $price - ($price * $discount / 100)), 2, '.', '');
    }

    public function getEffectiveDiscountAttribute(): float
    {
        $productDiscount = (float) $this->product_discount;
        if ($productDiscount > 0) {
            return $productDiscount;
        }

        $categoryDiscount = $this->relationLoaded('category')
            ? $this->category?->category_discount
            : $this->category()->value('category_discount');

        return (float) ($categoryDiscount ?? 0);
    }

    public static function getProductStatus(int $productId): bool
    {
        return (bool) self::whereKey($productId)->value('status');
    }
}
