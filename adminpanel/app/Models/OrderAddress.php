<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddress extends Model
{
    protected $fillable = [
        'order_id', 'user_address_id', 'label', 'recipient_name', 'phone', 'alternative_phone',
        'division_id', 'district_id', 'upazila_id', 'division_name', 'district_name',
        'upazila_name', 'area', 'postal_code', 'address_line',
    ];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function userAddress(): BelongsTo { return $this->belongsTo(UserAddress::class); }
}
