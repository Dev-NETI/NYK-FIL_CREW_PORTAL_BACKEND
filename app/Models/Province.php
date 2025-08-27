<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Province extends Model
{
    protected $fillable = [
        'region_id',
        'name',
        'code',
    ];

    /**
     * Get the region that owns the province.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the cities for the province.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    /**
     * Get the island through the region.
     */
    public function island(): HasManyThrough
    {
        return $this->hasManyThrough(Island::class, Region::class);
    }
}
