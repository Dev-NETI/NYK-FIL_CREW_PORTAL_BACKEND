<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrewCertificate extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'certificate_id',
        'crew_id',
        'grade',
        'rank_permitted',
        'certificate_no',
        'issued_by',
        'date_issued',
        'expiry_date',
        'file_path',
        'file_ext',
    ];

    protected $casts = [
        'date_issued' => 'date',
        'expiry_date' => 'date',
    ];

    /**
     * Get the certificate definition that this crew certificate belongs to.
     */
    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }

    /**
     * Get the crew profile that owns this certificate.
     */
    public function crew(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class, 'crew_id', 'crew_id');
    }

    /**
     * Get all update requests for this certificate.
     */
    public function updates()
    {
        return $this->hasMany(CrewCertificateUpdate::class);
    }

    /**
     * Get pending update requests for this certificate.
     */
    public function pendingUpdates()
    {
        return $this->hasMany(CrewCertificateUpdate::class)->where('status', 'pending');
    }

    /**
     * Scope to filter expired certificates.
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Scope to filter active (not expired) certificates.
     */
    public function scopeActive($query)
    {
        return $query->where('expiry_date', '>=', now())
            ->orWhereNull('expiry_date');
    }

    /**
     * Scope to filter certificates expiring soon (within specified days).
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereBetween('expiry_date', [
            now(),
            now()->addDays($days)
        ]);
    }

    /**
     * Check if the certificate is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    /**
     * Check if the certificate is expiring soon (within specified days).
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isFuture() &&
            $this->expiry_date->diffInDays(now()) <= $days;
    }

    /**
     * Get the number of days until expiry.
     */
    public function daysUntilExpiry(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return $this->expiry_date->diffInDays(now(), false);
    }
}
