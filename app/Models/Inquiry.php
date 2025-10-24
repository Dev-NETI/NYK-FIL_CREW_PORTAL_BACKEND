<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inquiry extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'crew_id',
        'department_id',
        'subject',
        'status',
        'modified_by',
    ];

    public function crew(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function departmentCategory(): BelongsTo
    {
        return $this->belongsTo(DepartmentCategory::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(InquiryMessage::class, 'inquiry_id')->orderBy('created_at', 'DESC');
    }
}
