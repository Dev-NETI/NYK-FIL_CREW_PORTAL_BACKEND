<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmploymentDocumentUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmploymentDocumentApprovalController extends Controller
{
    /**
     * Get all pending updates
     */
    public function index()
    {
        try {
            $updates = EmploymentDocumentUpdate::with([
                'employmentDocument.userProfile',
                'employmentDocument.employmentDocumentType',
                'userProfile',
            ])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

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
            $updates = EmploymentDocumentUpdate::with([
                'employmentDocument.userProfile',
                'employmentDocument.employmentDocumentType',
                'userProfile',
            ])
                ->orderBy('created_at', 'desc')
                ->get();

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
            $update = EmploymentDocumentUpdate::with([
                'employmentDocument.userProfile',
                'employmentDocument.employmentDocumentType',
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
            $update = EmploymentDocumentUpdate::with('employmentDocument')->findOrFail($id);

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

            // Apply changes to actual document
            $update->employmentDocument->update($update->updated_data);

            // Mark as approved
            $update->update([
                'status' => 'approved',
                'reviewed_by' => $reviewerName,
                'reviewed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Update approved and applied successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving update', [
                'error' => $e->getMessage(),
                'update_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

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
        try {
            $validated = $request->validate([
                'rejection_reason' => 'required|string|max:500',
            ]);

            $update = EmploymentDocumentUpdate::findOrFail($id);

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

            $update->update([
                'status' => 'rejected',
                'reviewed_by' => $reviewerName,
                'reviewed_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Update rejected',
            ]);
        } catch (\Exception $e) {
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
     * Get update history for a document
     */
    public function history($documentId)
    {
        try {
            $updates = EmploymentDocumentUpdate::with([
                'employmentDocument.userProfile',
                'employmentDocument.employmentDocumentType',
                'userProfile',
            ])
                ->where('employment_document_id', $documentId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $updates,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching update history', [
                'error' => $e->getMessage(),
                'document_id' => $documentId,
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
