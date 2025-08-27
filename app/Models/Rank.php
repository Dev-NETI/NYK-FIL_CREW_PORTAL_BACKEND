<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rank extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'rank_group_id',
        'name',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the rank group that owns this rank.
     */
    public function rankGroup(): BelongsTo
    {
        return $this->belongsTo(RankGroup::class);
    }

    /**
     * Get the rank category through the rank group.
     */
    public function rankCategory()
    {
        return $this->rankGroup->rankCategory();
    }

    /**
     * Get the contracts for this rank.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
