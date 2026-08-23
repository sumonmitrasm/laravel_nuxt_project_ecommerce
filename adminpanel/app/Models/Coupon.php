<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'code',
        'apply_method',
        'scope',
        'customer_scope',
        'discount_type',
        'discount_value',
        'maximum_discount',
        'minimum_order_amount',
        'minimum_lifetime_spend',
        'usage_limit',
        'usage_limit_per_user',
        'exclude_discounted_products',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'maximum_discount' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',
            'minimum_lifetime_spend' => 'decimal:2',
            'usage_limit' => 'integer',
            'usage_limit_per_user' => 'integer',
            'exclude_discounted_products' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_products')->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_categories')->withTimestamps();
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'coupon_brands')->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coupon_users')->withTimestamps();
    }

}
