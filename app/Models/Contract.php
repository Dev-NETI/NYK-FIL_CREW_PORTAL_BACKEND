<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Contract extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'contract_number',
        'user_id',
        'vessel_id',
        'rank_id',
        'departure_date',
        'arrival_date',
        'duration_months',
        'contract_start_date',
        'contract_end_date',
        'port_of_departure',
        'port_of_arrival',
        'basic_wage',
        'fixed_overtime',
        'leave_pay',
        'subsistence_allowance',
        'vacation_leave_conversion',
        'total_guaranteed_monthly',
        'currency',
        'contract_status',
        'remarks',
        'modified_by',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'arrival_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'duration_months' => 'integer',
        'basic_wage' => 'decimal:2',
        'fixed_overtime' => 'decimal:2',
        'leave_pay' => 'decimal:2',
        'subsistence_allowance' => 'decimal:2',
        'vacation_leave_conversion' => 'decimal:2',
        'total_guaranteed_monthly' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model and set up observers.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($contract) {
            // Auto-calculate contract end date if not set
            if ($contract->contract_start_date && $contract->duration_months && ! $contract->contract_end_date) {
                $contract->contract_end_date = Carbon::parse($contract->contract_start_date)
                    ->addMonths($contract->duration_months);
            }
        });
    }

    /**
     * Get the user (crew member) for this contract.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias for user() for backward compatibility.
     */
    public function crew(): BelongsTo
    {
        return $this->user();
    }

    /**
     * Get the vessel for this contract.
     */
    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    /**
     * Get the rank for this contract.
     */
    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    /**
     * Check if contract is currently active.
     */
    public function isActive(): bool
    {
        return $this->contract_start_date <= now() &&
            $this->contract_end_date >= now();
    }

    /**
     * Check if contract is expired.
     */
    public function isExpired(): bool
    {
        return $this->contract_end_date < now();
    }

    /**
     * Get days remaining in contract.
     */
    public function daysRemaining(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return max(0, now()->diffInDays($this->contract_end_date, false));
    }

    /**
     * Scope for active contracts.
     */
    public function scopeActive($query)
    {
        return $query->where('contract_start_date', '<=', now())
            ->where('contract_end_date', '>=', now());
    }

    /**
     * Scope for expired contracts.
     */
    public function scopeExpired($query)
    {
        return $query->where('contract_end_date', '<', now());
    }

    /**
     * Scope for contracts expiring soon.
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        $date = now()->addDays($days);

        return $query->where('contract_end_date', '<=', $date)
            ->where('contract_end_date', '>=', now());
    }
}
