<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartmentSlot extends Model
{
    use HasModifiedBy;

    protected $fillable = [
        'department_id',
        'date',
        'capacity',
        'opening_time',
        'closing_time',
        'interval_minutes',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'date' => 'date',
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'department_slot_id');
    }
}
