<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Island extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Get the regions for the island.
     */
    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }
}
