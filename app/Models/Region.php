<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = [
        'island_id',
        'name',
    ];

    /**
     * Get the island that owns the region.
     */
    public function island(): BelongsTo
    {
        return $this->belongsTo(Island::class);
    }

    /**
     * Get the provinces for the region.
     */
    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class);
    }
}
