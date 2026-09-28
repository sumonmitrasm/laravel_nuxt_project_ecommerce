<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $fillable = ['purchase_id', 'product_variant_id', 'product_name', 'sku', 'quantity', 'unit_cost', 'line_total'];
    protected function casts(): array { return ['unit_cost' => 'decimal:2', 'line_total' => 'decimal:2']; }
    public function purchase(): BelongsTo { return $this->belongsTo(Purchase::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
}
