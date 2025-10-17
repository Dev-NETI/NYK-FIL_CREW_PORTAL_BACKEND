<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'psgc_code',
        'reg_desc',
        'reg_code',
    ];
}
