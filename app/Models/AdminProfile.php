<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminProfile extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'user_id',
        'firstname',
        'middlename',
        'lastname',
        'modified_by',
    ];

    /**
     * Get the user that owns the admin profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who last modified this record.
     */
    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
