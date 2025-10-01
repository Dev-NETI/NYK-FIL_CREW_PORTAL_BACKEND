<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AlloteeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\CrewAlloteeController;
use App\Http\Controllers\Api\FleetController;
use App\Http\Controllers\Api\IslandController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\UserProgramEmploymentController;
use App\Http\Controllers\Api\RankCategoryController;
use App\Http\Controllers\Api\RankController;
use App\Http\Controllers\Api\RankGroupController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\UniversityController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VesselController;
use App\Http\Controllers\Api\VesselTypeController;
use App\Http\Controllers\JobDescriptionRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'initiateLogin']);
    Route::post('verify', [AuthController::class, 'verifyOtpAndLogin']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);
});

// Protected routes (common for both crew and admin)
Route::middleware(['auth:sanctum'])->group(function () {
    // User info and auth management
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // User profile routes
    Route::get('/crew/{crewId}/profile', [UserController::class, 'getProfile']);
});

// Crew-only routes (requires is_crew = 1)
Route::middleware(['auth:sanctum', 'crew'])->prefix('crew')->group(function () {
    // Crew-specific endpoints
    Route::get('/dashboard', function () {
        return response()->json([
            'success' => true,
            'message' => 'Welcome to crew dashboard!',
            'redirect_to' => '/home'
        ]);
    });

    // Crew can view their own data
    Route::apiResource('contracts', ContractController::class)->only(['index', 'show']);
    Route::apiResource('crew-allotees', CrewAlloteeController::class)->only(['index', 'show']);
});

// Admin-only routes (requires is_crew = 0)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Admin dashboard
    Route::get('/dashboard', function () {
        return response()->json([
            'success' => true,
            'message' => 'Welcome to admin dashboard!',
            'redirect_to' => '/admin'
        ]);
    });
    Route::apiResource('vessel-types', VesselTypeController::class);
    Route::apiResource('universities', UniversityController::class);
    Route::apiResource('rank-categories', RankCategoryController::class);
    Route::apiResource('rank-groups', RankGroupController::class);
    Route::apiResource('ranks', RankController::class);
    Route::apiResource('fleets', FleetController::class);
    Route::apiResource('islands', IslandController::class);
    Route::apiResource('regions', RegionController::class);
    Route::apiResource('provinces', ProvinceController::class);
    Route::apiResource('cities', CityController::class);
    Route::apiResource('vessels', VesselController::class);
    Route::apiResource('addresses', AddressController::class);
    Route::apiResource('allotees', AlloteeController::class);
    Route::apiResource('contracts', ContractController::class);
    Route::apiResource('crew-allotees', CrewAlloteeController::class);
    Route::apiResource('programs', ProgramController::class);

    // User employment records
    Route::get('crew/{userId}/employment', [UserProgramEmploymentController::class, 'index']);
    Route::post('crew/{userId}/employment', [UserProgramEmploymentController::class, 'store']);
    Route::get('crew/{userId}/employment/{employment}', [UserProgramEmploymentController::class, 'show']);
    Route::put('crew/{userId}/employment/{employment}', [UserProgramEmploymentController::class, 'update']);
    Route::delete('crew/{userId}/employment/{employment}', [UserProgramEmploymentController::class, 'destroy']);

    Route::apiResource('crew', UserController::class);
    Route::get('/crew/{id}/profile', [UserController::class, 'getProfileAdmin']);
    // Route::apiResource('job-description-requests', JobDescriptionRequestController::class);
});
