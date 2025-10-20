<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barangay extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'brgy_code',
        'brgy_desc',
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

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'citymun_code', 'citymun_code');
    }
}
