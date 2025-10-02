<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentCategory extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'name',
    ];
}
