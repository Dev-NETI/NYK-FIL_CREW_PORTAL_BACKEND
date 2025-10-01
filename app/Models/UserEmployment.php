<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserEmployment extends Model
{
    use HasFactory, HasModifiedBy, SoftDeletes;
    
    protected $table = 'user_employment';
    
    protected $fillable = [
        'user_id',
        'fleet_id',
        'rank_id',
        'crew_status',
        'hire_status',
        'hire_date',
        'passport_number',
        'passport_expiry',
        'seaman_book_number',
        'seaman_book_expiry',
        'primary_allotee_id',
        'basic_salary',
        'employment_notes',
    ];
    
    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'passport_expiry' => 'date',
            'seaman_book_expiry' => 'date',
            'basic_salary' => 'decimal:2',
            'deleted_at' => 'datetime',
        ];
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class);
    }
    
    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }
    
    public function primaryAllotee(): BelongsTo
    {
        return $this->belongsTo(Allotee::class, 'primary_allotee_id');
    }
}
