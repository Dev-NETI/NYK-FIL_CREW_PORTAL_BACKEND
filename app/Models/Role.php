<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasModifiedBy, SoftDeletes;

    protected $fillable = [
        'name',
        'modified_by',
    ];
}
