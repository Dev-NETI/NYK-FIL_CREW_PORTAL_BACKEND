<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WageScale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rank_id',
        'vessel_type_id',
        'effective_date',
        'basic_wage',
        'fixed_overtime',
        'leave_pay',
        'subsistence_allowance',
        'vacation_leave_conversion',
        'total_guaranteed_monthly',
        'currency',
        'modified_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'basic_wage' => 'decimal:2',
        'fixed_overtime' => 'decimal:2',
        'leave_pay' => 'decimal:2',
        'subsistence_allowance' => 'decimal:2',
        'vacation_leave_conversion' => 'decimal:2',
        'total_guaranteed_monthly' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    public function vesselType(): BelongsTo
    {
        return $this->belongsTo(VesselType::class);
    }
}
