<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'department_category_id',
        'name',
    ];

    public function departmentCategory(): BelongsTo
    {
        return $this->belongsTo(DepartmentCategory::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function fleets(): HasMany
    {
        return $this->hasMany(Fleet::class);
    }
}
