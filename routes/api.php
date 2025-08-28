<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AlloteeController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\CrewAlloteeController;
use App\Http\Controllers\Api\FleetController;
use App\Http\Controllers\Api\IslandController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\RankCategoryController;
use App\Http\Controllers\Api\RankController;
use App\Http\Controllers\Api\RankGroupController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\UniversityController;
use App\Http\Controllers\Api\VesselController;
use App\Http\Controllers\Api\VesselTypeController;
use App\Http\Controllers\JobDescriptionRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Temporary public routes for testing job description requests
// Route::post('job-description-requests', [JobDescriptionRequestController::class, 'store']);
// Route::get('job-description-requests', [JobDescriptionRequestController::class, 'index']);
Route::apiResource('job-description-requests', JobDescriptionRequestController::class);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
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
    // Route::apiResource('job-description-requests', JobDescriptionRequestController::class);
});
