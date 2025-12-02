<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificateType extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'name',
    ];

    /**
     * Get all certificates that belong to this type.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
