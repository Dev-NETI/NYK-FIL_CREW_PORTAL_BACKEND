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
        'rank_department_id',
        'rank_type_id',
        'name',
        'code',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the rank department that owns this rank.
     */
    public function rankDepartment(): BelongsTo
    {
        return $this->belongsTo(RankDepartment::class);
    }

    /**
     * Get the rank type that owns this rank.
     */
    public function rankType(): BelongsTo
    {
        return $this->belongsTo(RankType::class);
    }

    /**
     * Get the contracts for this rank.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
