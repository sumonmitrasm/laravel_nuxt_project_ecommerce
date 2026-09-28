<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = ['supplier_id', 'admin_id', 'purchase_number', 'purchase_date', 'total_amount', 'paid_amount', 'due_amount', 'note'];
    protected function casts(): array { return ['purchase_date' => 'date', 'total_amount' => 'decimal:2', 'paid_amount' => 'decimal:2', 'due_amount' => 'decimal:2']; }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function items(): HasMany { return $this->hasMany(PurchaseItem::class); }
    public function payments(): HasMany { return $this->hasMany(SupplierPayment::class); }
}
