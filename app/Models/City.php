<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'province_id',
        'name',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the province that owns the city.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the addresses in this city.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the universities in this city.
     */
    public function universities(): HasMany
    {
        return $this->hasMany(University::class);
    }

    /**
     * Alias for universities() for backward compatibility.
     */
    public function schools(): HasMany
    {
        return $this->universities();
    }

    /**
     * Get the region through the province.
     */
    public function region()
    {
        return $this->province->region();
    }

    /**
     * Get the island through the province and region.
     */
    public function island()
    {
        return $this->province->region->island();
    }
}
