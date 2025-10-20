<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'psgc_code',
        'reg_desc',
        'reg_code',
    ];

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class, 'reg_code', 'reg_code');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'reg_code', 'reg_code');
    }

    public function barangays(): HasMany
    {
        return $this->hasMany(Barangay::class, 'reg_code', 'reg_code');
    }
}
