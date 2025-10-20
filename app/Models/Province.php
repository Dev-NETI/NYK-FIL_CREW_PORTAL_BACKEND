<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Province extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'psgc_code',
        'prov_desc',
        'reg_code',
        'prov_code',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'reg_code', 'reg_code');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'prov_code', 'prov_code');
    }

    public function barangays(): HasMany
    {
        return $this->hasMany(Barangay::class, 'prov_code', 'prov_code');
    }
}
