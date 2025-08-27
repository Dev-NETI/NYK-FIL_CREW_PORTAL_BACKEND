<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasModifiedBy;

class Allotee extends Model
{
    use SoftDeletes, HasModifiedBy;
    protected $fillable = [
        'name',
        'relationship',
        'mobile_number',
        'email',
        'address',
        'date_of_birth',
        'gender',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the users (crew members) that have this allotee.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'crew_allotees')
            ->using(CrewAllotee::class)
            ->withPivot([
                'is_primary',
                'is_emergency_contact',
                'deleted_at'
            ])
            ->withTimestamps();
    }

    /**
     * Alias for users() for backward compatibility.
     */
    public function crew(): BelongsToMany
    {
        return $this->users();
    }

    /**
     * Get the users who have this as primary allotee.
     */
    public function primaryFor(): HasMany
    {
        return $this->hasMany(User::class, 'primary_allotee_id');
    }
}
