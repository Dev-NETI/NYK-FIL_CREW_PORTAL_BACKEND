<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vessel extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'name',
        'vessel_id', // vessel id from mpip
        'vessel_type_id',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

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
        return $this->contracts()
            ->where('contract_start_date', '<=', now())
            ->where('contract_end_date', '>=', now());
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
        )->where('contracts.contract_start_date', '<=', now())
            ->where('contracts.contract_end_date', '>=', now());
    }
}
