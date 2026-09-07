<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = ['name', 'code', 'description', 'charge', 'delivery_time', 'icon', 'position', 'status'];

    protected function casts(): array
    {
        return ['charge' => 'decimal:2', 'position' => 'integer', 'status' => 'boolean'];
    }
}
