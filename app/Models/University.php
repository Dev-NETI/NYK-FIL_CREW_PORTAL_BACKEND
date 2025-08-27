<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class University extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the users (crew members) who graduated from this university.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'graduated_school_id');
    }

    /**
     * Alias for users() for backward compatibility.
     */
    public function crew(): HasMany
    {
        return $this->users();
    }
}
