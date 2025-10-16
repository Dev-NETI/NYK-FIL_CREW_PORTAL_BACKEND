<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminRole extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'user_id',
        'role_id',
        'modified_by',
    ];

    /**
     * Get the user that owns the admin role.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role associated with this admin role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
