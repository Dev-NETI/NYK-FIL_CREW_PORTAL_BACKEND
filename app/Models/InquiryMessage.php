<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Check if user is authenticated before accessing
            if (Auth::check()) {
                $model->is_staff_reply = Auth::user()->is_crew ? false : true;
            }

            $model->read_at = null;
        });
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
