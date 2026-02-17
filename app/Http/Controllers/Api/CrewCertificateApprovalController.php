<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CrewCertificateUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CrewCertificateApprovalController extends Controller
{
    /**
     * Get all pending updates
     */
    public function index()
    {
        try {
            $updates = CrewCertificateUpdate::with([
                'crewCertificate.crew',
                'crewCertificate.certificate',
                'userProfile',
            ])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            // For records without certificate relationship, add certificate name from Certificate model
            $updates->each(function ($update) {
                if (!$update->crewCertificate && isset($update->updated_data['certificate_id'])) {
                    $certificate = \App\Models\Certificate::find($update->updated_data['certificate_id']);
                    if ($certificate) {
                        // Add certificate name to updated_data if not already present
                        if (!isset($update->updated_data['certificate_name'])) {
                            $updateData = $update->updated_data;
                            $updateData['certificate_name'] = $certificate->name;
                            $update->updated_data = $updateData;
                        }
                    }
                }
            });

            return response()->json($updates);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending updates',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all updates (pending, approved, rejected)
     */
    public function all()
    {
        try {
            $updates = CrewCertificateUpdate::with([
                'crewCertificate.crew',
                'crewCertificate.certificate',
                'userProfile',
            ])
                ->orderBy('created_at', 'desc')
                ->get();

            // For rejected records without certificate relationship, add certificate name from Certificate model
            $updates->each(function ($update) {
                if (!$update->crewCertificate && isset($update->updated_data['certificate_id'])) {
                    $certificate = \App\Models\Certificate::find($update->updated_data['certificate_id']);
                    if ($certificate) {
                        // Add certificate name to updated_data if not already present
                        if (!isset($update->updated_data['certificate_name'])) {
                            $updateData = $update->updated_data;
                            $updateData['certificate_name'] = $certificate->name;
                            $update->updated_data = $updateData;
                        }
                    }
                }
            });

            return response()->json($updates);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch updates',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single update request
     */
    public function show($id)
    {
        try {
            $update = CrewCertificateUpdate::with([
                'crewCertificate.crew',
                'crewCertificate.certificate',
                'userProfile',
            ])->findOrFail($id);

            return response()->json($update);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update request not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Approve update
     */
    public function approve(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $update = CrewCertificateUpdate::with('crewCertificate')->findOrFail($id);

            if ($update->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This update has already been processed',
                ], 400);
            }

            $user = Auth::guard('sanctum')->user();
            $reviewerName = $user->adminProfile
                ? "{$user->adminProfile->firstname} {$user->adminProfile->lastname}"
                : $user->email;

            // Check if this is a new certificate creation (original certificate_no starts with PENDING_)
            $isNewCertificate = isset($update->original_data['certificate_no']) &&
                str_starts_with($update->original_data['certificate_no'], 'PENDING_');

            // Apply changes to actual certificate
            $updateData = $update->updated_data;

            // If there's a pending file, move it to the permanent location
            if (isset($updateData['file_path']) && str_contains($updateData['file_path'], 'crew_certificates_pending')) {
                $oldPath = $updateData['file_path'];
                $newPath = str_replace('crew_certificates_pending', 'crew_certificates', $oldPath);

                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->move($oldPath, $newPath);
                    $updateData['file_path'] = $newPath;
                }
            }

            $update->crewCertificate->update($updateData);

            // Mark as approved
            $update->update([
                'status' => 'approved',
                'reviewed_by' => $reviewerName,
                'reviewed_at' => now(),
            ]);

            DB::commit();

            $message = $isNewCertificate
                ? 'New certificate approved and created successfully'
                : 'Update approved and applied successfully';

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve update',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject update
     */
    public function reject(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'rejection_reason' => 'required|string|max:500',
            ]);

            $update = CrewCertificateUpdate::with('crewCertificate')->findOrFail($id);

            if ($update->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This update has already been processed',
                ], 400);
            }

            $user = Auth::guard('sanctum')->user();
            $reviewerName = $user->adminProfile
                ? "{$user->adminProfile->firstname} {$user->adminProfile->lastname}"
                : $user->email;

            // Check if this is a new certificate creation (original certificate_no starts with PENDING_)
            $isNewCertificate = isset($update->original_data['certificate_no']) &&
                str_starts_with($update->original_data['certificate_no'], 'PENDING_');

            // Mark update as rejected first (before deleting certificate to avoid cascade delete)
            $update->update([
                'status' => 'rejected',
                'reviewed_by' => $reviewerName,
                'reviewed_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            // If rejecting a new certificate creation, delete the temporary certificate and pending file
            if ($isNewCertificate) {
                // Delete pending file if exists
                if (isset($update->updated_data['file_path'])) {
                    $filePath = $update->updated_data['file_path'];
                    if (Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                }

                // Remove the foreign key constraint temporarily by setting it to null
                // This prevents cascade delete of the update record
                $update->update(['crew_certificate_id' => null]);

                // Now delete the temporary crew certificate
                $update->crewCertificate->forceDelete();
            }

            DB::commit();

            $message = $isNewCertificate
                ? 'New certificate creation rejected and removed'
                : 'Update rejected';

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting update', [
                'error' => $e->getMessage(),
                'update_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject update',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get update history for a certificate
     */
    public function history($certificateId)
    {
        try {
            $updates = CrewCertificateUpdate::with([
                'crewCertificate.crew',
                'crewCertificate.certificate',
                'userProfile',
            ])
                ->where('crew_certificate_id', $certificateId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $updates,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching update history', [
                'error' => $e->getMessage(),
                'certificate_id' => $certificateId,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch update history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
