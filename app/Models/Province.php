<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Province extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'region_id',
        'name',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
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
