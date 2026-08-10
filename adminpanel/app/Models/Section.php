<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Section extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    /** Active root categories belonging to this section. */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)
            ->where('parent_id', 0)
            ->where('status', true)
            ->with(['subcategories', 'products.brand'])
            ->orderBy('position')
            ->orderBy('category_name');
    }

    public static function sections(): array
    {
        return self::with('categories')
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }
}
