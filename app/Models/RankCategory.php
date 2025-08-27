<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RankCategory extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the rank groups for this category.
     */
    public function rankGroups(): HasMany
    {
        return $this->hasMany(RankGroup::class);
    }

    /**
     * Get all ranks through rank groups.
     */
    public function ranks()
    {
        return $this->hasManyThrough(Rank::class, RankGroup::class);
    }
}
