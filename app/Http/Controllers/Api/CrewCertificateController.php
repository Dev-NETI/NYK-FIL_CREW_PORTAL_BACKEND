<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CrewCertificate;
use App\Models\CrewCertificateUpdate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CrewCertificateController extends Controller
{
    /**
     * Display a listing of crew certificates.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CrewCertificate::with(['certificate.certificateType', 'crew']);

        // Filter by crew_id if provided
        if ($request->has('crew_id')) {
            $query->where('crew_id', $request->crew_id);
        }

        $crewCertificates = $query->get();

        return response()->json($crewCertificates);
    }

    /**
     * Store a newly created crew certificate.
     */
    public function store(Request $request): JsonResponse
    {
        // Get the selected certificate to apply conditional validation
        $certificate = null;
        if ($request->filled('certificate_id')) {
            $certificate = Certificate::find($request->certificate_id);
        }

        // Build validation rules
        $rules = [
            'crew_id' => 'required|string|exists:user_profiles,crew_id',
            'certificate_id' => 'required|integer|exists:certificates,id',
            'certificate_no' => 'nullable|string|min:3|max:100',
            'issued_by' => 'nullable|string|min:3|max:255',
            'date_issued' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:date_issued',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif|max:5120', // 5MB max
        ];

        // Conditional validation for grade (required if stcw_type is "COC")
        if ($certificate && $certificate->stcw_type === 'COC') {
            $rules['grade'] = 'required|string|min:2|max:255';
        } else {
            $rules['grade'] = 'nullable|string|max:255';
        }

        // Conditional validation for rank_permitted (required if certificate_type_id is 6)
        if ($certificate && $certificate->certificate_type_id === 6) {
            $rules['rank_permitted'] = 'required|string|min:2|max:255';
        } else {
            $rules['rank_permitted'] = 'nullable|string|max:255';
        }

        // Validate the request
        $validated = $request->validate($rules, [
            'crew_id.required' => 'Crew ID is required',
            'crew_id.exists' => 'The selected crew does not exist',
            'certificate_id.required' => 'Certificate is required',
            'certificate_id.exists' => 'The selected certificate does not exist',
            'certificate_no.min' => 'Certificate number must be at least 3 characters',
            'certificate_no.max' => 'Certificate number must not exceed 100 characters',
            'issued_by.min' => 'Issuing authority must be at least 3 characters',
            'issued_by.max' => 'Issuing authority must not exceed 255 characters',
            'grade.required' => 'Grade is required for COC certificates',
            'grade.min' => 'Grade must be at least 2 characters',
            'grade.max' => 'Grade must not exceed 255 characters',
            'rank_permitted.required' => 'Rank permitted is required for this certificate type',
            'rank_permitted.min' => 'Rank permitted must be at least 2 characters',
            'rank_permitted.max' => 'Rank permitted must not exceed 255 characters',
            'expiry_date.after' => 'Expiry date must be after date issued',
            'file.mimes' => 'Only PDF and image files are allowed',
            'file.max' => 'File size must not exceed 5MB',
        ]);

        // Check if user is crew or admin
        if (Auth::guard('sanctum')->user()->is_crew == 1) {
            // Crew creating new certificate: Create pending approval request
            // First, create a temporary crew certificate to hold the reference
            $tempData = [
                'crew_id' => $validated['crew_id'],
                'certificate_id' => $validated['certificate_id'],
                'certificate_no' => 'PENDING_' . time(), // Temporary placeholder
                'issued_by' => $validated['issued_by'] ?? null,
                'date_issued' => $validated['date_issued'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
            ];

            $crewCertificate = CrewCertificate::create($tempData);

            // Prepare data for approval
            $newData = [
                'certificate_id' => $validated['certificate_id'],
                'certificate_name' => $certificate->name ?? null, // Store certificate name for later reference
                'certificate_no' => $validated['certificate_no'] ?? null,
                'issued_by' => $validated['issued_by'] ?? null,
                'date_issued' => $validated['date_issued'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'grade' => $validated['grade'] ?? null,
                'rank_permitted' => $validated['rank_permitted'] ?? null,
            ];

            // Handle file upload if present
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('crew_certificates_pending', 'public');
                $newData['file_path'] = $path;
                $newData['file_ext'] = $file->getClientOriginalExtension();
            }

            // Create pending update request
            $update = CrewCertificateUpdate::create([
                'crew_certificate_id' => $crewCertificate->id,
                'crew_id' => $validated['crew_id'],
                'original_data' => $tempData,
                'updated_data' => $newData,
                'status' => 'pending',
            ]);

            // Load the relationship for the response
            $update->load('userProfile', 'crewCertificate.certificate');

            return response()->json([
                'success' => true,
                'message' => 'New certificate submitted for admin approval',
                'data' => $update
            ], 201);
        } else {
            // Admin creating certificate: Direct creation without approval
            // Handle file upload if present
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('crew_certificates', 'public');
                $validated['file_path'] = $path;
                $validated['file_ext'] = $file->getClientOriginalExtension();
            }

            // Remove 'file' from validated data before creating record
            unset($validated['file']);

            // Create the crew certificate
            $crewCertificate = CrewCertificate::create($validated);

            // Load relationships for response
            $crewCertificate->load(['certificate.certificateType', 'crew']);

            return response()->json([
                'success' => true,
                'message' => 'Certificate added successfully',
                'data' => $crewCertificate
            ], 201);
        }
    }

    /**
     * Display the specified crew certificate.
     */
    public function show(int $id): JsonResponse
    {
        $crewCertificate = CrewCertificate::with(['certificate.certificateType', 'crew'])
            ->findOrFail($id);

        return response()->json($crewCertificate);
    }

    /**
     * Display crew certificates by crew_id with optional filtering.
     */
    public function showByCrewId(Request $request, string $crewId): JsonResponse
    {
        $query = CrewCertificate::with(['certificate.certificateType', 'crew'])
            ->where('crew_id', $crewId);

        // Filter by certificate type
        if ($request->filled('certificate_type_id')) {
            $query->whereHas('certificate', function ($q) use ($request) {
                $q->where('certificate_type_id', $request->certificate_type_id);
            });
        }

        // Search by certificate name, number, or issuing authority
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('certificate_no', 'like', "%{$search}%")
                    ->orWhere('issued_by', 'like', "%{$search}%")
                    ->orWhereHas('certificate', function ($certQ) use ($search) {
                        $certQ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        $crewCertificates = $query->with('pendingUpdates')->orderBy('created_at', 'desc')->get();

        // Transform the data to include computed fields
        $transformedCertificates = $crewCertificates->map(function ($cert) {
            $data = $cert->toArray();

            // Check if certificate has pending approval
            $hasPendingApproval = $cert->pendingUpdates->count() > 0;
            $isPendingCertificate = str_starts_with($cert->certificate_no ?? '', 'PENDING_');

            // Add status based on expiry date and approval status
            if ($hasPendingApproval || $isPendingCertificate) {
                $status = 'pending_approval';
            } elseif ($cert->expiry_date) {
                if ($cert->isExpired()) {
                    $status = 'expired';
                } elseif ($cert->isExpiringSoon(60)) { // 60 days
                    $status = 'expiring_soon';
                } else {
                    $status = 'valid';
                }
            } else {
                $status = 'valid';
            }
            $data['status'] = $status;

            // Add computed fields
            $data['has_file'] = !empty($cert->file_path);
            $data['days_until_expiry'] = $cert->daysUntilExpiry();
            $data['has_pending_approval'] = $hasPendingApproval || $isPendingCertificate;
            $data['is_pending_certificate'] = $isPendingCertificate;

            return $data;
        });

        return response()->json([
            'success' => true,
            'data' => $transformedCertificates,
            'count' => $transformedCertificates->count()
        ]);
    }

    /**
     * Update the specified crew certificate.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $crewCertificate = CrewCertificate::findOrFail($id);

        // Get the certificate for conditional validation
        $certificateId = $request->filled('certificate_id') ? $request->certificate_id : $crewCertificate->certificate_id;
        $certificate = Certificate::find($certificateId);

        // Build validation rules
        $rules = [
            'certificate_id' => 'sometimes|integer|exists:certificates,id',
            'certificate_no' => 'nullable|string|min:3|max:100',
            'issued_by' => 'nullable|string|min:3|max:255',
            'date_issued' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:date_issued',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif|max:5120',
        ];

        // Conditional validation for grade
        if ($certificate && $certificate->stcw_type === 'COC') {
            $rules['grade'] = 'required|string|min:2|max:255';
        } else {
            $rules['grade'] = 'nullable|string|max:255';
        }

        // Conditional validation for rank_permitted
        if ($certificate && $certificate->certificate_type_id === 6) {
            $rules['rank_permitted'] = 'required|string|min:2|max:255';
        } else {
            $rules['rank_permitted'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        // Check if user is crew or admin
        if (Auth::guard('sanctum')->user()->is_crew == 1) {
            // Crew update: Create pending approval request
            $updatedData = [
                'certificate_id' => $validated['certificate_id'] ?? $crewCertificate->certificate_id,
                'certificate_name' => $certificate->name ?? $crewCertificate->certificate->name ?? null, // Store certificate name
                'certificate_no' => $validated['certificate_no'] ?? $crewCertificate->certificate_no,
                'issued_by' => $validated['issued_by'] ?? $crewCertificate->issued_by,
                'date_issued' => $validated['date_issued'] ?? $crewCertificate->date_issued,
                'expiry_date' => $validated['expiry_date'] ?? $crewCertificate->expiry_date,
                'grade' => $validated['grade'] ?? $crewCertificate->grade,
                'rank_permitted' => $validated['rank_permitted'] ?? $crewCertificate->rank_permitted,
            ];

            // Handle file upload if present
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('crew_certificates_pending', 'public');
                $updatedData['file_path'] = $path;
                $updatedData['file_ext'] = $file->getClientOriginalExtension();
            }

            // Create pending update
            $update = CrewCertificateUpdate::create([
                'crew_certificate_id' => $crewCertificate->id,
                'crew_id' => $crewCertificate->crew_id,
                'original_data' => $crewCertificate->only([
                    'certificate_id', 'certificate_no', 'issued_by',
                    'date_issued', 'expiry_date', 'grade', 'rank_permitted',
                    'file_path', 'file_ext'
                ]),
                'updated_data' => $updatedData,
                'status' => 'pending',
            ]);

            // Load the relationship for the response
            $update->load('userProfile', 'crewCertificate.certificate');

            return response()->json([
                'success' => true,
                'message' => 'Update submitted for admin approval',
                'data' => $update
            ]);
        } else {
            // Admin update: Direct update without approval
            // Handle file upload if present
            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($crewCertificate->file_path && Storage::disk('public')->exists($crewCertificate->file_path)) {
                    Storage::disk('public')->delete($crewCertificate->file_path);
                }

                $file = $request->file('file');
                $path = $file->store('crew_certificates', 'public');
                $validated['file_path'] = $path;
                $validated['file_ext'] = $file->getClientOriginalExtension();
            }

            // Remove 'file' from validated data
            unset($validated['file']);

            // Update the crew certificate
            $crewCertificate->update($validated);

            // Reload relationships
            $crewCertificate->load(['certificate.certificateType', 'crew']);

            return response()->json([
                'success' => true,
                'message' => 'Certificate updated successfully',
                'data' => $crewCertificate
            ]);
        }
    }

    /**
     * Remove the specified crew certificate.
     */
    public function destroy(int $id): JsonResponse
    {
        $crewCertificate = CrewCertificate::findOrFail($id);

        // Delete associated file if exists
        if ($crewCertificate->file_path && Storage::disk('public')->exists($crewCertificate->file_path)) {
            Storage::disk('public')->delete($crewCertificate->file_path);
        }

        $crewCertificate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Certificate deleted successfully'
        ]);
    }

    /**
     * View/download the certificate file.
     */
    public function viewFile(int $id)
    {
        $crewCertificate = CrewCertificate::findOrFail($id);

        if (!$crewCertificate->file_path || !Storage::disk('public')->exists($crewCertificate->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
        }

        return Storage::disk('public')->response($crewCertificate->file_path);
    }
}
