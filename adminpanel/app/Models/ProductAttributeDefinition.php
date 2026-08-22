<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductAttributeDefinition extends Model
{
    protected $table = 'attributes';
    protected $fillable = ['name', 'slug', 'type', 'position', 'status'];
    protected function casts(): array { return ['status' => 'boolean']; }
    public function values(): HasMany { return $this->hasMany(ProductAttributeValue::class, 'attribute_id'); }
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_attribute', 'attribute_id', 'category_id')
            ->withPivot(['is_variant', 'is_filterable', 'is_required', 'position']);
    }
}
