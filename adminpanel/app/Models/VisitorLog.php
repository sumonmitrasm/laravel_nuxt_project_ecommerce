<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'visitor_id', 'ip_address', 'path', 'page_title', 'country', 'city', 'device', 'referrer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
