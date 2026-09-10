<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    protected $fillable = ['admin_id', 'order_id', 'type', 'title', 'message', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function admin(): BelongsTo { return $this->belongsTo(Admin::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
