<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPayment extends Model
{
    protected $fillable = [
        'order_id', 'method', 'transaction_id', 'gateway_reference', 'amount',
        'currency', 'status', 'paid_at', 'gateway_response',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'datetime', 'gateway_response' => 'array'];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
