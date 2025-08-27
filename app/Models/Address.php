<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'type',
        'street_address',
        'barangay',
        'city_id',
        'zip_code',
        'landmark',
        'latitude',
        'longitude',
        'modified_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the city where this address is located.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the user who last modified this address.
     */
    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    /**
     * Get the users (crew members) with this as permanent address.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'permanent_address_id');
    }

    /**
     * Alias for users() for backward compatibility.
     */
    public function crew(): HasMany
    {
        return $this->users();
    }

    /**
     * Get the allotees with this address.
     */
    public function allotees(): HasMany
    {
        return $this->hasMany(Allotee::class);
    }

    /**
     * Get the complete address string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = [
            $this->street_address,
            $this->barangay,
            $this->city->name ?? '',
            $this->city->province->name ?? '',
            $this->zip_code
        ];

        return implode(', ', array_filter($parts));
    }
}
