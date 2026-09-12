<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    protected $fillable = ['sku', 'price', 'stock', 'low_stock_threshold', 'status'];
    protected function casts(): array { return ['price' => 'decimal:2', 'stock' => 'integer', 'low_stock_threshold' => 'integer', 'status' => 'boolean']; }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function stockAdjustments() { return $this->hasMany(StockAdjustment::class); }
    public function values(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'attribute_value_product_variant',
            'product_variant_id',
            'attribute_value_id'
        );
    }
}
