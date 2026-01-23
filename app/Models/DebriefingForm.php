<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebriefingForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'crew_id',
        'department_id',
        'status',

        'rank',
        'crew_name',
        'vessel_type',
        'principal_name',

        'embarkation_vessel_name',
        'embarkation_place',
        'embarkation_date',
        'disembarkation_date',
        'disembarkation_place',
        'manila_arrival_date',

        'present_address',
        'provincial_address',
        'phone_number',
        'email',
        'date_of_availability',
        'availability_status',
        'next_vessel_assignment_date',
        'long_vacation_reason',

        'has_illness_or_injury',
        'illness_injury_types',
        'lost_work_days',
        'medical_incident_details',

        'comment_q1_technical',
        'comment_q2_crewing',
        'comment_q3_complaint',
        'comment_q4_immigrant_visa',
        'comment_q5_commitments',
        'comment_q6_additional',

        'signature_path',

        'submitted_at',
        'confirmed_at',
        'confirmed_by',

        'pdf_path',
        'pdf_generated_at',
    ];


    protected $casts = [
        'has_illness_or_injury' => 'boolean',
        'illness_injury_types' => 'array',

        'embarkation_date' => 'date',
        'disembarkation_date' => 'date',
        'manila_arrival_date' => 'date',
        'date_of_availability' => 'date',
        'next_vessel_assignment_date' => 'date',

        'submitted_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'pdf_generated_at' => 'datetime',
    ];

    public function crewProfile()
    {
        return $this->belongsTo(UserProfile::class, 'crew_id');
    }

    public function confirmedByUser()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
    public function crew()
    {
        return $this->belongsTo(User::class, 'crew_id');
    }

}
