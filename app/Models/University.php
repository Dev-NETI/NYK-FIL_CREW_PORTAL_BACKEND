<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class University extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
