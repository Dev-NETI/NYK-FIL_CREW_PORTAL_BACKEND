<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasModifiedBy;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasModifiedBy, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'is_crew',
        'email',
        'password',
        'crew_id',
        'fleet_id',
        'rank_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'date_of_birth',
        'age',
        'gender',
        'mobile_number',
        'permanent_address_id',
        'graduated_school_id',
        'date_graduated',
        'crew_status',
        'hire_status',
        'hire_date',
        'passport_number',
        'passport_expiry',
        'seaman_book_number',
        'seaman_book_expiry',
        'primary_allotee_id',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'date_graduated' => 'date',
            'hire_date' => 'date',
            'passport_expiry' => 'date',
            'seaman_book_expiry' => 'date',
            'age' => 'integer',
            'is_crew' => 'boolean',
            'deleted_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Boot the model and set up observers.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($user) {
            // Auto-calculate age from date of birth
            if ($user->date_of_birth) {
                $user->age = Carbon::parse($user->date_of_birth)->age;
            }
        });
    }

    /**
     * Get the permanent address.
     */
    public function permanentAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'permanent_address_id');
    }

    /**
     * Get the graduated university.
     */
    public function graduatedUniversity(): BelongsTo
    {
        return $this->belongsTo(University::class, 'graduated_school_id');
    }

    /**
     * Alias for graduatedUniversity() for backward compatibility.
     */
    public function graduatedSchool(): BelongsTo
    {
        return $this->graduatedUniversity();
    }

    /**
     * Get the primary allotee.
     */
    public function primaryAllotee(): BelongsTo
    {
        return $this->belongsTo(Allotee::class, 'primary_allotee_id');
    }

    /**
     * Get the fleet.
     */
    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class);
    }

    /**
     * Get the rank.
     */
    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    /**
     * Get all allotees for this user (crew member).
     */
    public function allotees(): BelongsToMany
    {
        return $this->belongsToMany(Allotee::class, 'crew_allotees')
            ->using(CrewAllotee::class)
            ->withPivot([
                'is_primary',
                'is_emergency_contact',
                'deleted_at',
            ])
            ->withTimestamps();
    }

    /**
     * Get all contracts for this user (crew member).
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Get the current active contract.
     */
    public function currentContract()
    {
        return $this->contracts()
            ->where('contract_start_date', '<=', now())
            ->where('contract_end_date', '>=', now())
            ->first();
    }

    /**
     * Get emergency contacts.
     */
    public function emergencyContacts()
    {
        return $this->allotees()->wherePivot('is_emergency_contact', true);
    }

    /**
     * Get beneficiaries (all active allotees).
     */
    public function beneficiaries()
    {
        return $this->allotees()->wherePivotNull('deleted_at');
    }

    /**
     * Get the current vessel assignment.
     */
    public function currentVessel()
    {
        $contract = $this->currentContract();

        return $contract ? $contract->vessel : null;
    }

    /**
     * Get the current rank.
     */
    public function currentRank()
    {
        return $this->rank;
    }

    /**
     * Check if user is currently employed as crew.
     */
    public function isEmployed(): bool
    {
        return $this->currentContract() !== null;
    }

    /**
     * Get the full name of the user.
     */
    public function getFullNameAttribute(): string
    {
        $nameParts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ]);

        return implode(' ', $nameParts);
    }

    /**
     * Get the display name (for backward compatibility).
     */
    public function getNameAttribute(): string
    {
        return $this->getFullNameAttribute();
    }

    /**
     * Check if user is a crew member.
     */
    public function isCrew(): bool
    {
        return $this->is_crew === true;
    }

    /**
     * Check if user is an employee (non-crew).
     */
    public function isEmployee(): bool
    {
        return $this->is_crew === false;
    }

    /**
     * Check if user type is unspecified.
     */
    public function isUnspecifiedType(): bool
    {
        return $this->is_crew === null;
    }

    /**
     * Scope for crew members on board.
     */
    public function scopeOnBoard($query)
    {
        return $query->where('crew_status', 'on_board');
    }

    /**
     * Scope for crew members on vacation.
     */
    public function scopeOnVacation($query)
    {
        return $query->where('crew_status', 'on_vacation');
    }

    /**
     * Scope for new hire crew members.
     */
    public function scopeNewHire($query)
    {
        return $query->where('hire_status', 'new_hire');
    }

    /**
     * Scope for re-hire crew members.
     */
    public function scopeReHire($query)
    {
        return $query->where('hire_status', 're_hire');
    }

    /**
     * Scope for crew members.
     */
    public function scopeCrew($query)
    {
        return $query->where('is_crew', true);
    }

    /**
     * Scope for employees (non-crew users).
     */
    public function scopeEmployees($query)
    {
        return $query->where('is_crew', false);
    }

    /**
     * Scope for users with unspecified type (is_crew is null).
     */
    public function scopeUnspecifiedType($query)
    {
        return $query->whereNull('is_crew');
    }

    /**
     * Get the user's OTP verifications.
     */
    public function otpVerifications()
    {
        return $this->hasMany(OtpVerification::class);
    }

    /**
     * Get valid OTP verifications for this user.
     */
    public function validOtpVerifications()
    {
        return $this->otpVerifications()->valid();
    }
}
