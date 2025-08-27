<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RankGroup extends Model
{
    protected $fillable = [
        'rank_category_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the rank category that owns this group.
     */
    public function rankCategory(): BelongsTo
    {
        return $this->belongsTo(RankCategory::class);
    }

    /**
     * Get the ranks in this group.
     */
    public function ranks(): HasMany
    {
        return $this->hasMany(Rank::class);
    }
}
