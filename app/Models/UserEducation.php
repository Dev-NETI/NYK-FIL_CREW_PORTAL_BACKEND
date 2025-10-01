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
        'graduated_school_id',
        'date_graduated',
        'degree',
        'field_of_study',
        'gpa',
        'education_level',
        'certifications',
        'additional_training',
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
    
    public function graduatedSchool(): BelongsTo
    {
        return $this->belongsTo(University::class, 'graduated_school_id');
    }
    
    public function graduatedUniversity(): BelongsTo
    {
        return $this->graduatedSchool();
    }
}
