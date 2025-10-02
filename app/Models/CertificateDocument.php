<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificateDocument extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'crew_id',
        'certificate_document_type_id',
        'modified_by',
        'certificate',
        'certificate_no',
        'issuing_authority',
        'date_issued',
        'expiry_date',
    ];

    protected $casts = [
        'date_issued' => 'date',
        'expiry_date' => 'date',
    ];

    public function crew(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class, 'crew_id');
    }

    public function certificateDocumentType(): BelongsTo
    {
        return $this->belongsTo(CertificateDocumentType::class);
    }
}
