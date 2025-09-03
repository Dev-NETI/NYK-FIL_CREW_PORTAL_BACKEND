<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'otp_hash',
        'session_token',
        'expires_at',
        'attempts',
        'ip_address',
        'user_agent',
        'verified_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'attempts' => 'integer'
    ];

    protected $hidden = [
        'otp_hash',
        'session_token'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->isExpired() && $this->attempts < 5 && is_null($this->verified_at);
    }

    public function markAsVerified(): void
    {
        $this->update([
            'verified_at' => Carbon::now()
        ]);
    }

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', Carbon::now())
                    ->where('attempts', '<', 5)
                    ->whereNull('verified_at');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', Carbon::now());
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    protected static function booted()
    {
        static::creating(function ($otpVerification) {
            $otpVerification->created_at = Carbon::now();
            $otpVerification->updated_at = Carbon::now();
        });
    }
}