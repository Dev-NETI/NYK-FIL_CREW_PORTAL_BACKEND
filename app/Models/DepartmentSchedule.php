<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentSchedule extends Model
{
    use SoftDeletes;

    protected $table = 'department_schedules';

    protected $fillable = [
        'department_id',
        'date',
        'total_slots',
        'opening_time',
        'closing_time',
        'slot_duration_minutes',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Appointments under this schedule
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'schedule_id');
    }
}
