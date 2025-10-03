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
                $user = Auth::user();
                $model->modified_by = $user->first_name . ' ' . $user->last_name || '';
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $user = Auth::user();
                $model->modified_by = $user->first_name . ' ' . $user->last_name || '';
            }
        });
    }
}
