<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmploymentDocumentUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

            // Check if this is a new document creation (original document_number starts with PENDING_)
            $isNewDocument = isset($update->original_data['document_number']) &&
                str_starts_with($update->original_data['document_number'], 'PENDING_');

            // Apply changes to actual document
            $updateData = $update->updated_data;

            // If there's a pending file, move it to the permanent location
            if (isset($updateData['file_path']) && str_contains($updateData['file_path'], 'employment_documents_pending')) {
                $oldPath = $updateData['file_path'];
                $newPath = str_replace('employment_documents_pending', 'employment_documents', $oldPath);

                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->move($oldPath, $newPath);
                    $updateData['file_path'] = $newPath;
                }
            }

            $update->employmentDocument->update($updateData);

            // Mark as approved
            $update->update([
                'status' => 'approved',
                'reviewed_by' => $reviewerName,
                'reviewed_at' => now(),
            ]);

            DB::commit();

            $message = $isNewDocument
                ? 'New employment document approved and created successfully'
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

            // Check if this is a new document creation (original document_number starts with PENDING_)
            $isNewDocument = isset($update->original_data['document_number']) &&
                str_starts_with($update->original_data['document_number'], 'PENDING_');

            // If rejecting a new document creation, delete the temporary document and pending file
            if ($isNewDocument) {
                // Delete pending file if exists
                if (isset($update->updated_data['file_path'])) {
                    $filePath = $update->updated_data['file_path'];
                    if (Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                }

                // Delete the temporary employment document
                $update->employmentDocument->forceDelete();
            }

            $update->update([
                'status' => 'rejected',
                'reviewed_by' => $reviewerName,
                'reviewed_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            DB::commit();

            $message = $isNewDocument
                ? 'New document creation rejected and removed'
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

    /**
     * View pending document file
     */
    public function viewPendingFile($id)
    {
        try {
            $update = EmploymentDocumentUpdate::findOrFail($id);

            // Check if there's a pending file
            if (!isset($update->updated_data['file_path'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending file found',
                ], 404);
            }

            $filePath = $update->updated_data['file_path'];

            // Check if file exists
            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found',
                ], 404);
            }

            // Get file extension and determine content type
            $extension = $update->updated_data['file_ext'] ?? pathinfo($filePath, PATHINFO_EXTENSION);
            $mimeType = match (strtolower($extension)) {
                'pdf' => 'application/pdf',
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'application/octet-stream',
            };

            // Return file response
            return response()->file(
                Storage::disk('public')->path($filePath),
                [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error viewing pending file', [
                'error' => $e->getMessage(),
                'update_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load file',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
