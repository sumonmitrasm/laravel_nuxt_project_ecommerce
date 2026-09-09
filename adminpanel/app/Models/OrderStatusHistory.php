<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    protected $fillable = ['order_id', 'status', 'note', 'changed_by_type', 'changed_by_id'];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
