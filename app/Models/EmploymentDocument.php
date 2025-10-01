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
    ];

    public function crew(): BelongsTo
    {
        return $this->belongsTo(User::class, 'crew_id');
    }

    public function employmentDocumentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentDocumentType::class);
    }
}
