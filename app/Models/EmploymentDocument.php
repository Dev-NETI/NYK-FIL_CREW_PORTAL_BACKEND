<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmploymentDocument extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'crew_id',
        'employment_document_type_id',
        'document_number',
        'file_path',
        'file_ext',
    ];

    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class, 'crew_id', 'crew_id');
    }

    public function employmentDocumentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentDocumentType::class);
    }
}
