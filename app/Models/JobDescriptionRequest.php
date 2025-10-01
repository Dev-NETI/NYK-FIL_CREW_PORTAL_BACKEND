<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class JobDescriptionRequest extends Model
{
    use HasFactory, HasModifiedBy, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'crew_id',
        'purpose',
        'visa_type',
        'notes',
        'status',
        'memo_no',
        'processed_by',
        'processed_date',
        'approved_by',
        'approved_date',
        'disapproval_reason',
        'vp_comments',
        'signature_added',
    ];

    protected function casts(): array
    {
        return [
            'processed_date' => 'datetime',
            'approved_date' => 'datetime',
            'signature_added' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = static::generateJobDescriptionId();
            }
        });
    }

    public static function generateJobDescriptionId(): string
    {
        $year = date('Y');
        $latestRequest = static::withTrashed()
            ->where('id', 'like', "JD-{$year}-%")
            ->orderByDesc('id')
            ->first();
        
        if ($latestRequest) {
            $lastNumber = (int) substr($latestRequest->id, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return sprintf('JD-%s-%03d', $year, $nextNumber);
    }

    public function crew(): BelongsTo
    {
        return $this->belongsTo(User::class, 'crew_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getFormattedPurposeAttribute(): string
    {
        $purposes = [
            'SSS' => 'Social Security System (SSS)',
            'PAG_IBIG' => 'Pag-Ibig Fund',
            'PHILHEALTH' => 'PhilHealth',
            'VISA' => 'VISA Application',
        ];

        $basePurpose = $purposes[$this->purpose] ?? $this->purpose;

        if ($this->purpose === 'VISA' && $this->visa_type) {
            $visaTypes = [
                'TOURIST' => 'Tourist Visa',
                'BUSINESS' => 'Business Visa',
                'WORK' => 'Work Visa',
                'TRANSIT' => 'Transit Visa',
                'STUDENT' => 'Student Visa',
                'FAMILY' => 'Family/Dependent Visa',
                'SEAMAN' => "Seaman's Visa",
            ];
            
            return $visaTypes[$this->visa_type] ?? "{$basePurpose} ({$this->visa_type})";
        }

        return $basePurpose;
    }

    public function generateMemoNumber(): string
    {
        if ($this->memo_no) {
            return $this->memo_no;
        }

        $year = date('Y');
        $latestMemo = static::withTrashed()
            ->where('memo_no', 'like', "NYK-JD-{$year}-%")
            ->orderByDesc('memo_no')
            ->first();
        
        if ($latestMemo) {
            $lastNumber = (int) substr($latestMemo->memo_no, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        $memoNo = sprintf('NYK-JD-%s-%03d', $year, $nextNumber);
        $this->update(['memo_no' => $memoNo]);
        
        return $memoNo;
    }

    public function canBeProcessed(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'ready_for_approval';
    }

    public function canBeDownloaded(): bool
    {
        return $this->status === 'approved' && $this->signature_added;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeReadyForApproval($query)
    {
        return $query->where('status', 'ready_for_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeDisapproved($query)
    {
        return $query->where('status', 'disapproved');
    }

    public function scopeForCrew($query, $crewId)
    {
        return $query->where('crew_id', $crewId);
    }
}
