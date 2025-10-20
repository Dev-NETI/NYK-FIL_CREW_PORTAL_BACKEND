<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'name',
        'modified_by',
    ];

    /**
     * Get all admin role assignments for this role.
     */
    public function adminRoles(): HasMany
    {
        return $this->hasMany(AdminRole::class);
    }

    /**
     * Get all users that have this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'admin_roles')
            ->withPivot(['modified_by'])
            ->withTimestamps();
    }
}
