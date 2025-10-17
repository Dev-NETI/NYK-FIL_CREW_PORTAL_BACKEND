<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class UserProfile extends Model
{
    use HasFactory, HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'user_id',
        'crew_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birth_date',
        'birth_place',
        'age',
        'gender',
        'nationality',
        'civil_status',
        'religion',
        'blood_type'
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'age' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($profile) {
            // Auto-calculate age from date of birth
            if ($profile->date_of_birth) {
                $profile->age = Carbon::parse($profile->date_of_birth)->age;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employmentDocuments(): HasMany
    {
        return $this->hasMany(EmploymentDocument::class, 'crew_id', 'crew_id');
    }

    public function travelDocuments(): HasMany
    {
        return $this->hasMany(TravelDocument::class, 'crew_id', 'crew_id');
    }

    public function certificateDocuments(): HasMany
    {
        return $this->hasMany(CertificateDocument::class, 'crew_id', 'crew_id');
    }

    public function getFullNameAttribute(): string
    {
        $nameParts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ]);

        return implode(' ', $nameParts);
    }
}
