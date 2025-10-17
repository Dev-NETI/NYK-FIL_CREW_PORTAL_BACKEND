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
                'employmentDocument.crew.crewProfile',
                'employmentDocument.position',
                'employmentDocument.vessel',
                'crew.crewProfile',
            ])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $updates,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pending updates', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending updates',
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
                'employmentDocument.crew.crewProfile',
                'employmentDocument.position',
                'employmentDocument.vessel',
                'crew.crewProfile',
                'reviewer.adminProfile',
            ])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $updates,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching all updates', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch updates',
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
                'employmentDocument.crew.crewProfile',
                'employmentDocument.position',
                'employmentDocument.vessel',
                'crew.crewProfile',
                'reviewer.adminProfile',
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $update,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update request not found',
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

            // Apply changes to actual document
            $update->employmentDocument->update($update->updated_data);

            // Mark as approved
            $update->update([
                'status' => 'approved',
                'reviewed_by' => Auth::guard('sanctum')->user()->id,
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
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve update',
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

            $update->update([
                'status' => 'rejected',
                'reviewed_by' => Auth::guard('sanctum')->user()->id,
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
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject update',
            ], 500);
        }
    }

    /**
     * Get update history for a document
     */
    public function history($documentId)
    {
        try {
            $updates = EmploymentDocumentUpdate::with(['reviewer.adminProfile'])
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
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch update history',
            ], 500);
        }
    }
}
