<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $appends = ['image_url', 'effective_discount', 'final_price'];

    protected $fillable = [
        'section_id', 'category_id', 'brand_id', 'admin_id', 'vendor_id', 'admin_type',
        'product_name', 'product_code', 'product_price', 'product_discount',
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

    public static function getDiscountPrice(int $productId, ?int $variantId = null): float
    {
        $product = self::with('category:id,category_discount')
            ->whereKey($productId)
            ->first(['id', 'product_price', 'product_discount', 'category_id']);

        if (! $product) {
            return 0;
        }

        $discount = $product->effective_discount;

        $regularPrice = (float) $product->product_price;
        if ($variantId) {
            $variantPrice = ProductVariant::whereKey($variantId)
                ->where('product_id', $product->id)
                ->value('price');
            if ($variantPrice !== null) $regularPrice = (float) $variantPrice;
        }

        return (float) $product->discountedPrice($regularPrice);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->product_image
            ? asset('admin/productimage/'.basename($this->product_image))
            : null;
    }

    public function getFinalPriceAttribute(): string
    {
        return $this->discountedPrice((float) $this->product_price);
    }

    public function regularPriceForVariant(?ProductVariant $variant): string
    {
        $price = $variant && $variant->price !== null
            ? (float) $variant->price
            : (float) $this->product_price;

        return number_format($price, 2, '.', '');
    }

    public function finalPriceForVariant(?ProductVariant $variant): string
    {
        return $this->discountedPrice((float) $this->regularPriceForVariant($variant));
    }

    public function discountedPrice(float $regularPrice): string
    {
        $discount = $this->effective_discount;
        $finalPrice = max(0, $regularPrice - ($regularPrice * $discount / 100));

        return number_format($finalPrice, 2, '.', '');
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

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'attribute_value_product',
            'product_id',
            'attribute_value_id'
        );
    }
}
