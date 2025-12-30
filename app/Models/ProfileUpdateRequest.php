<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfileUpdateRequest extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'crew_id',
        'section',
        'current_data',
        'requested_data',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'current_data' => 'array',
            'requested_data' => 'array',
            'reviewed_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // Relationships
    public function crew(): BelongsTo
    {
        return $this->belongsTo(User::class, 'crew_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Query Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForCrew($query, $crewId)
    {
        return $query->where('crew_id', $crewId);
    }

    public function scopeBySection($query, $section)
    {
        return $query->where('section', $section);
    }

    // Status Methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function approve($reviewerId): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject($reviewerId, $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
