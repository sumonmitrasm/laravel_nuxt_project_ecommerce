<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReturn extends Model
{
    protected $fillable = [
        'order_id', 'user_id', 'reason', 'status', 'admin_note',
        'refund_amount', 'requested_at', 'processed_at',
    ];

    protected function casts(): array
    {
        return ['refund_amount' => 'decimal:2', 'requested_at' => 'datetime', 'processed_at' => 'datetime'];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
