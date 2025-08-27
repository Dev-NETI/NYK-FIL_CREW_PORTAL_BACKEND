<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'island_id',
        'name',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
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
