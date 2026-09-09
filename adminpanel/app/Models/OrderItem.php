<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_variant_id', 'product_name', 'product_code',
        'sku', 'image', 'options', 'quantity', 'regular_price', 'unit_price',
        'discount_amount', 'line_total',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array', 'quantity' => 'integer', 'regular_price' => 'decimal:2',
            'unit_price' => 'decimal:2', 'discount_amount' => 'decimal:2', 'line_total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
}
