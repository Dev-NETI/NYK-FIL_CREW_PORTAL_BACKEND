<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificate extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'certificate_type_id',
        'regulation',
        'name',
        'stcw_type',
        'code',
        'vessel_type',
        'nmc_type',
        'nmc_department',
        'rank',
    ];

    public function certificateType(): BelongsTo
    {
        return $this->belongsTo(CertificateType::class);
    }
}
