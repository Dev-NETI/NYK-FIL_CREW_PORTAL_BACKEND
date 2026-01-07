<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_id',
        'appointment_type_id',
        'schedule_id',
        'date',
        'time',
        'purpose',
        'duration_minutes',
        'status',
        'created_by',
        'created_by_type',
    ];

    protected $casts = [
        'date' => 'date',
        'deleted_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AppointmentType::class, 'appointment_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cancellations(): HasMany
    {
        return $this->hasMany(AppointmentCancellation::class, 'appointment_id');
    }

    public function schedule()
    {
        return $this->belongsTo(DepartmentSchedule::class, 'schedule_id');
    }

}
