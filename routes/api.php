<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminRoleController;
use App\Http\Controllers\Api\AlloteeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CertificateDocumentController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\CrewAlloteeController;
use App\Http\Controllers\Api\DepartmentCategoryController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DepartmentTypesController;
use App\Http\Controllers\Api\EmploymentDocumentApprovalController;
use App\Http\Controllers\Api\EmploymentDocumentController;
use App\Http\Controllers\Api\EmploymentDocumentTypeController;
use App\Http\Controllers\Api\FleetController;
use App\Http\Controllers\Api\GeographyController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\IslandController;
use App\Http\Controllers\Api\NationalityController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\UserProgramEmploymentController;
use App\Http\Controllers\Api\RankCategoryController;
use App\Http\Controllers\Api\RankController;
use App\Http\Controllers\Api\RankGroupController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TravelDocumentApprovalController;
use App\Http\Controllers\Api\TravelDocumentController;
use App\Http\Controllers\Api\TravelDocumentTypeController;
use App\Http\Controllers\Api\UniversityController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserEducationController;
use App\Http\Controllers\Api\VesselController;
use App\Http\Controllers\Api\VesselTypeController;
use App\Http\Controllers\JobDescriptionRequestController;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'initiateLogin']);
    Route::post('verify', [AuthController::class, 'verifyOtpAndLogin']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);
});

//no middleware for testing
Route::apiResource('employment-documents', EmploymentDocumentController::class)->only(['index', 'show', 'update', 'store', 'destroy']);
Route::get('employment-documents/{id}/view-file', [EmploymentDocumentController::class, 'viewFile']);
Route::apiResource('employment-document-types', EmploymentDocumentTypeController::class)->only(['index']);
Route::apiResource('travel-documents', TravelDocumentController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
Route::get('travel-documents/{id}/view-file', [TravelDocumentController::class, 'viewFile']);
Route::apiResource('travel-document-types', TravelDocumentTypeController::class)->only(['index']);
Route::apiResource('certificate-documents', CertificateDocumentController::class);
Route::apiResource('department-categories', DepartmentCategoryController::class)->only(['index']);
Route::apiResource('departments', DepartmentController::class)->only(['index', 'show']);
Route::apiResource('admins', AdminController::class);

// Geography API routes (public access)
Route::prefix('geography')->group(function () {
    Route::get('regions', [GeographyController::class, 'getRegions']);
    Route::get('provinces', [GeographyController::class, 'getProvincesByRegion']);
    Route::get('cities', [GeographyController::class, 'getCitiesByProvince']);
    Route::get('barangays', [GeographyController::class, 'getBarangaysByCity']);

    Route::get('region/{regCode}', [GeographyController::class, 'getRegionByCode']);
    Route::get('province/{provCode}', [GeographyController::class, 'getProvinceByCode']);
    Route::get('city/{cityCode}', [GeographyController::class, 'getCityByCode']);
    Route::get('barangay/{brgyCode}', [GeographyController::class, 'getBarangayByCode']);
});

Route::apiResource('admin-roles', AdminRoleController::class)->only(['index', 'store', 'destroy']);
Route::get('admin-roles/user/{userId}', [AdminRoleController::class, 'getByUserId']);
Route::apiResource('roles', RoleController::class)->only(['index']);

//For Inquiry
Route::apiResource('departmentTypes', DepartmentTypesController::class)->only(['index']);
Route::get('department/{id}', [DepartmentTypesController::class, 'viewDepartments']);
Route::apiResource('inquiry', InquiryController::class)->only(['show', 'store']);
// Employment document approvals
Route::get('employment-document-updates', [EmploymentDocumentApprovalController::class, 'index']);
Route::get('employment-document-updates/all', [EmploymentDocumentApprovalController::class, 'all']);
Route::get('employment-document-updates/{id}', [EmploymentDocumentApprovalController::class, 'show']);
Route::post('employment-document-updates/{id}/approve', [EmploymentDocumentApprovalController::class, 'approve']);
Route::post('employment-document-updates/{id}/reject', [EmploymentDocumentApprovalController::class, 'reject']);
Route::get('employment-document-updates/history/{documentId}', [EmploymentDocumentApprovalController::class, 'history']);

// Travel document approvals
Route::get('travel-document-updates', [TravelDocumentApprovalController::class, 'index']);
Route::get('travel-document-updates/all', [TravelDocumentApprovalController::class, 'all']);
Route::get('travel-document-updates/{id}', [TravelDocumentApprovalController::class, 'show']);
Route::post('travel-document-updates/{id}/approve', [TravelDocumentApprovalController::class, 'approve']);
Route::post('travel-document-updates/{id}/reject', [TravelDocumentApprovalController::class, 'reject']);
Route::get('travel-document-updates/history/{documentId}', [TravelDocumentApprovalController::class, 'history']);

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

    // Crew can manage their own addresses
    Route::apiResource('addresses', AddressController::class);

    // Crew can view their own data
    Route::apiResource('contracts', ContractController::class)->only(['index', 'show']);
    Route::apiResource('crew-allotees', CrewAlloteeController::class)->only(['index', 'show']);
    
    // Crew education management (crew can only access their own education)
    Route::get('/education', function(Request $request) {
        return app(UserEducationController::class)->index(auth()->id());
    });
    Route::post('/education', function(Request $request) {
        return app(UserEducationController::class)->store($request, auth()->id());
    });
    Route::get('/education/{educationId}', function(Request $request, $educationId) {
        return app(UserEducationController::class)->show(auth()->id(), $educationId);
    });
    Route::put('/education/{educationId}', function(Request $request, $educationId) {
        return app(UserEducationController::class)->update($request, auth()->id(), $educationId);
    });
    Route::delete('/education/{educationId}', function(Request $request, $educationId) {
        return app(UserEducationController::class)->destroy(auth()->id(), $educationId);
    });
});

// VERY NICE
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
    Route::apiResource('nationalities', NationalityController::class);
    Route::apiResource('vessels', VesselController::class);
    Route::apiResource('addresses', AddressController::class);
    Route::apiResource('allotees', AlloteeController::class);
    Route::apiResource('contracts', ContractController::class);
    Route::apiResource('crew-allotees', CrewAlloteeController::class);
    Route::apiResource('employment-documents', EmploymentDocumentController::class);
    Route::apiResource('travel-documents', TravelDocumentController::class);
    Route::apiResource('programs', ProgramController::class);
    Route::apiResource('crew', UserController::class);

    // User employment records
    Route::get('crew/{userId}/employment', [UserProgramEmploymentController::class, 'index']);
    Route::post('crew/{userId}/employment', [UserProgramEmploymentController::class, 'store']);
    Route::get('crew/{userId}/employment/{employment}', [UserProgramEmploymentController::class, 'show']);
    Route::put('crew/{userId}/employment/{employment}', [UserProgramEmploymentController::class, 'update']);
    Route::delete('crew/{userId}/employment/{employment}', [UserProgramEmploymentController::class, 'destroy']);

    // User education management (admin can manage any user's education)
    Route::get('crew/{userId}/education', [UserEducationController::class, 'index']);
    Route::post('crew/{userId}/education', [UserEducationController::class, 'store']);
    Route::get('crew/{userId}/education/{educationId}', [UserEducationController::class, 'show']);
    Route::put('crew/{userId}/education/{educationId}', [UserEducationController::class, 'update']);
    Route::delete('crew/{userId}/education/{educationId}', [UserEducationController::class, 'destroy']);

    Route::get('/crew/{id}/profile', [UserController::class, 'getProfileAdmin']);
});

// recruitment post api
Route::post('crew/recruitment', [UserController::class, 'store']);
