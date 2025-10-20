<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmploymentDocumentUpdate extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'employment_document_id',
        'crew_id',
        'original_data',
        'updated_data',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'original_data' => 'array',
        'updated_data' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function employmentDocument()
    {
        return $this->belongsTo(EmploymentDocument::class);
    }

    public function userProfile()
    {
        return $this->belongsTo(UserProfile::class, 'crew_id', 'crew_id');
    }

    public function crew()
    {
        return $this->belongsTo(UserProfile::class, 'crew_id', 'crew_id');
    }
}
