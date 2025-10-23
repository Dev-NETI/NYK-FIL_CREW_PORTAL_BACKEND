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
            // Only set modified_by if it's null or empty
            if (Auth::check() && empty($model->modified_by)) {
                $user = Auth::guard('sanctum')->user();

                // For admin users, use their full name
                if ($user && $user->is_crew === 0 && $user->adminProfile) {
                    $model->modified_by = $user ? $user->id : 'System';
                } else {
                    // For crew users or if admin profile doesn't exist, use user ID
                    $model->modified_by = $user ? $user->id : 'System';
                }
            }
        });

        static::updating(function ($model) {
            // Always update modified_by on updates unless it was explicitly set in the update
            if (Auth::check()) {
                $user = Auth::guard('sanctum')->user();

                // For admin users, use their full name
                if ($user && $user->is_crew === 0 && $user->adminProfile) {
                    $model->modified_by = $user ? $user->id : 'System';
                } else {
                    // For crew users or if admin profile doesn't exist, use user ID
                    $model->modified_by = $user ? $user->id : 'System';
                }
            }
        });
    }
}
