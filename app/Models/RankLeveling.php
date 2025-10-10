<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RankLeveling extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'rank_id',
        'level',
    ];

    protected $casts = [
        'level' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the rank that owns this leveling.
     */
    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }
}
