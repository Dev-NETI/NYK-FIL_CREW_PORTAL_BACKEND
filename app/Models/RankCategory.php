<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RankCategory extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
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
