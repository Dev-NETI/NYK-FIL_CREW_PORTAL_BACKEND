<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserEducation extends Model
{
    use HasFactory, HasModifiedBy, SoftDeletes;

    protected $table = 'user_education';

    protected $fillable = [
        'user_id',
        'school_name',
        'date_graduated',
        'degree',
        'education_level',
    ];

    protected function casts(): array
    {
        return [
            'date_graduated' => 'date',
            'gpa' => 'decimal:2',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function graduatedUniversity(): BelongsTo
    {
        return $this->graduatedSchool();
    }
}
