<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    protected $fillable = ['name', 'code', 'description', 'charge', 'delivery_time', 'icon', 'position', 'status'];

    protected function casts(): array
    {
        return ['charge' => 'decimal:2', 'position' => 'integer', 'status' => 'boolean'];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
