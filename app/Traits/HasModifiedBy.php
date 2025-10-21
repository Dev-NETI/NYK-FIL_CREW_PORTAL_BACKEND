<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasModifiedBy
{
    /**
     * Boot the trait and set up model event listeners.
     */
    protected static function bootHasModifiedBy()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $user = Auth::guard('sanctum')->user();
                $model->modified_by = "{$user->adminProfile->firstname} {$user->adminProfile->lastname}" || '';
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $user = Auth::guard('sanctum')->user();
                $model->modified_by = "{$user->adminProfile->firstname} {$user->adminProfile->lastname}" || '';
            }
        });
    }
}
