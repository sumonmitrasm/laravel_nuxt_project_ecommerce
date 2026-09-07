<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone',
        'alternative_phone',
        'division',
        'district',
        'upazila',
        'area',
        'postal_code',
        'address_line',
        'is_default',
        'status',
    ];

    protected $appends = [
        'division_name',
        'district_name',
        'upazila_name',
    ];

    protected function casts(): array
    {
        return [
            'division' => 'integer',
            'district' => 'integer',
            'upazila' => 'integer',
            'is_default' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function locationDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division');
    }

    public function locationDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district');
    }

    public function locationUpazila(): BelongsTo
    {
        return $this->belongsTo(Upazila::class, 'upazila');
    }

    public function getDivisionNameAttribute(): ?string
    {
        return $this->locationDivision?->name;
    }

    public function getDistrictNameAttribute(): ?string
    {
        return $this->locationDistrict?->name;
    }

    public function getUpazilaNameAttribute(): ?string
    {
        return $this->locationUpazila?->name;
    }
}