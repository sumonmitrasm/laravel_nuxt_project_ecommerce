<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $fillable = ['name', 'color_code', 'status'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }
}
