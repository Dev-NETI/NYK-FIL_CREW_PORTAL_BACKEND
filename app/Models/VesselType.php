<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VesselType extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the vessels of this type.
     */
    public function vessels(): HasMany
    {
        return $this->hasMany(Vessel::class);
    }
}
