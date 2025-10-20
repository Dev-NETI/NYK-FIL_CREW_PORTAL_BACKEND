<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'psgc_code',
        'citymun_desc',
        'reg_code',
        'prov_code',
        'citymun_code',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'reg_code', 'reg_code');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'prov_code', 'prov_code');
    }

    public function barangays(): HasMany
    {
        return $this->hasMany(Barangay::class, 'citymun_code', 'citymun_code');
    }
}
