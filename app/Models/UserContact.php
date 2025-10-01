<?php

namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserContact extends Model
{
    use HasFactory, HasModifiedBy, SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'mobile_number',
        'alternate_phone',
        'email_personal',
        'permanent_address_id',
        'current_address_id',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
    ];
    
    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function permanentAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'permanent_address_id');
    }
    
    public function currentAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'current_address_id');
    }
}
