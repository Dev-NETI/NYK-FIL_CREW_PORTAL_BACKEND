<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelDocument extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'crew_id',
        'id_no',
        'travel_document_type_id',
        'place_of_issue',
        'date_of_issue',
        'expiration_date',
        'remaining_pages',
        'is_US_VISA',
        'visa_type'
    ];

    protected $casts = [
        'date_of_issue' => 'date',
        'expiration_date' => 'date',
        'remaining_pages' => 'integer',
    ];

    public function crew(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class, 'crew_id', 'crew_id');
    }

    public function travelDocumentType(): BelongsTo
    {
        return $this->belongsTo(TravelDocumentType::class);
    }
}
