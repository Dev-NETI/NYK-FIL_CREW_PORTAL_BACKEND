<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrewAllotee extends Pivot
{
    use SoftDeletes;

    protected $table = 'crew_allotees';

    protected $fillable = [
        'user_id',
        'allotee_id',
        'is_primary',
        'is_emergency_contact',
        'modified_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_emergency_contact' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user (crew member) that owns this relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the allotee that owns this relationship.
     */
    public function allotee(): BelongsTo
    {
        return $this->belongsTo(Allotee::class);
    }

    /**
     * Get the user who last modified this relationship.
     */
    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
