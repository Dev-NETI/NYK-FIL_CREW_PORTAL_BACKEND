<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InquiryMessage extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'inquiry_id',
        'user_id',
        'message',
        'is_staff_reply',
        'read_at',
        'modified_by',
    ];

    protected $casts = [
        'is_staff_reply' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
