<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'coupon_id', 'shipping_method_id', 'shipping_method_name',
        'payment_method', 'payment_status', 'order_status', 'subtotal', 'discount_amount',
        'shipping_charge', 'tax_amount', 'grand_total', 'currency', 'customer_note',
        'cancellation_reason', 'cancelled_at', 'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2', 'discount_amount' => 'decimal:2',
            'shipping_charge' => 'decimal:2', 'tax_amount' => 'decimal:2',
            'grand_total' => 'decimal:2', 'cancelled_at' => 'datetime', 'placed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function coupon(): BelongsTo { return $this->belongsTo(Coupon::class); }
    public function shippingMethod(): BelongsTo { return $this->belongsTo(ShippingMethod::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function address(): HasOne { return $this->hasOne(OrderAddress::class); }
    public function payments(): HasMany { return $this->hasMany(OrderPayment::class); }
    public function statusHistories(): HasMany { return $this->hasMany(OrderStatusHistory::class); }
}
