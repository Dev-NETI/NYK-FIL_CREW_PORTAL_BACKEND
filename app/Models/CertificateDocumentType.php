<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificateDocumentType extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'name',
        'modified_by',
    ];
}
