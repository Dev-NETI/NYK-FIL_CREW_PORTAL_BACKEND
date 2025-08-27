<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'street_address',
        'island_id',
        'region_id',
        'province_id',
        'city_id',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the island where this address is located.
     */
    public function island(): BelongsTo
    {
        return $this->belongsTo(Island::class);
    }

    /**
     * Get the region where this address is located.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the province where this address is located.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the city where this address is located.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
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
            $this->city->name ?? '',
            $this->province->name ?? '',
            $this->region->name ?? '',
            $this->island->name ?? '',
        ];

        return implode(', ', array_filter($parts));
    }
}
