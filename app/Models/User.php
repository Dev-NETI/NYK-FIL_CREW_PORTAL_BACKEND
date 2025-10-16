<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasModifiedBy;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'department_id',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'modified_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
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
    }

    /**
     * Get the user's profile information.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the admin's profile information.
     */
    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class);
    }

    /**
     * Get the user's physical traits.
     */
    public function physicalTraits(): HasOne
    {
        return $this->hasOne(UserPhysicalTrait::class);
    }

    /**
     * Get the user's contact information.
     */
    public function contacts(): HasOne
    {
        return $this->hasOne(UserContact::class);
    }

    /**
     * Get the user's employment information.
     */
    public function employment(): HasOne
    {
        return $this->hasOne(UserEmployment::class);
    }

    /**
     * Get the user's education information.
     */
    public function education(): HasOne
    {
        return $this->hasOne(UserEducation::class);
    }

    /**
     * Get the user's program employment records.
     */
    public function programEmployments(): HasMany
    {
        return $this->hasMany(UserProgramEmployment::class);
    }

    // Convenience methods for backward compatibility
    /**
     * Get the permanent address.
     */
    public function permanentAddress(): BelongsTo
    {
        return $this->contacts?->permanentAddress() ?? $this->belongsTo(Address::class, 'permanent_address_id');
    }

    /**
     * Get the graduated university.
     */
    public function graduatedUniversity(): BelongsTo
    {
        return $this->education?->graduatedUniversity() ?? $this->belongsTo(University::class, 'graduated_school_id');
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
        return $this->employment?->primaryAllotee() ?? $this->belongsTo(Allotee::class, 'primary_allotee_id');
    }

    /**
     * Get the fleet.
     */
    public function fleet(): BelongsTo
    {
        return $this->employment?->fleet() ?? $this->belongsTo(Fleet::class);
    }

    /**
     * Get the rank.
     */
    public function rank(): BelongsTo
    {
        return $this->employment?->rank() ?? $this->belongsTo(Rank::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the job designation.
     */
    public function jobDesignation(): BelongsTo
    {
        return $this->belongsTo(JobDesignation::class);
    }

    /**
     * Get the company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
        if ($this->profile) {
            return $this->profile->full_name;
        }

        return $this->email; // fallback to email if no profile
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

    /**
     * Get all admin role assignments for this user.
     */
    public function adminRoles(): HasMany
    {
        return $this->hasMany(AdminRole::class);
    }

    /**
     * Get all roles assigned to this user through admin_roles pivot table.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'admin_roles')
            ->withPivot(['modified_by'])
            ->withTimestamps();
    }
}
