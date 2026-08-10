<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
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
}
