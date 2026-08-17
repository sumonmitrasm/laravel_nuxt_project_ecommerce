<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductAttributeValue extends Model
{
    protected $table = 'attribute_values';

    protected $fillable = ['attribute_id', 'value', 'color_code', 'position', 'status'];
    protected function casts(): array { return ['status' => 'boolean']; }
    public function attribute(): BelongsTo { return $this->belongsTo(ProductAttributeDefinition::class, 'attribute_id'); }
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'attribute_value_product_variant',
            'attribute_value_id',
            'product_variant_id'
        );
    }
}
