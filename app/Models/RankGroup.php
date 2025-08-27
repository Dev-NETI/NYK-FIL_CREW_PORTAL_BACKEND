<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RankGroup extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'rank_category_id',
        'name',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
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
