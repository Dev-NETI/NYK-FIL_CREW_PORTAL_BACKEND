<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPhysicalTrait extends Model
{
    use HasFactory, HasModifiedBy, SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'height',
        'weight',
        'blood_type',
        'eye_color',
        'hair_color',
        'distinguishing_marks',
        'medical_conditions',
    ];
    
    protected function casts(): array
    {
        return [
            'height' => 'decimal:2',
            'weight' => 'decimal:2',
            'deleted_at' => 'datetime',
        ];
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
