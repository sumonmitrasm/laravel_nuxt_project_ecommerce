<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = ['admin_id', 'category', 'title', 'amount', 'expense_date', 'note'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'expense_date' => 'date']; }
    public function admin(): BelongsTo { return $this->belongsTo(Admin::class); }
}
