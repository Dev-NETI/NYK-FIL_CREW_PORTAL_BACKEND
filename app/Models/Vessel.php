<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vessel extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'vessel_id', // vessel id from mpip
        'vessel_type_id',
        'modified_by',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who last modified this vessel.
     */
    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    /**
     * Get the vessel type.
     */
    public function vesselType(): BelongsTo
    {
        return $this->belongsTo(VesselType::class);
    }

    /**
     * Get the contracts for this vessel.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Get the active contracts for this vessel.
     */
    public function activeContracts()
    {
        return $this->contracts()->where('status', 'active');
    }

    /**
     * Get the current crew on this vessel.
     */
    public function currentCrew()
    {
        return $this->hasManyThrough(
            User::class,
            Contract::class,
            'vessel_id',
            'id',
            'id',
            'user_id'
        )->where('contracts.status', 'active');
    }
}
