<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmploymentDocument;
use App\Models\EmploymentDocumentUpdate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmploymentDocumentController extends Controller
{
    public function index(): JsonResponse
    {
        $employmentDocuments = EmploymentDocument::with(['crew', 'employmentDocumentType'])->get();

        return response()->json($employmentDocuments);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'crew_id' => 'required',
            'employment_document_type_id' => 'required',
            'document_number' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp|max:5120', // 5MB max
        ]);

        // Handle file upload if present
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('employment_documents', 'public');
            $validated['file_path'] = $path;
            $validated['file_ext'] = $file->getClientOriginalExtension();
        }

        // Remove 'file' from validated data before creating record
        unset($validated['file']);

        $store = EmploymentDocument::create($validated);

        return response()->json([
            'success' => $store ? true : false,
            'message' => $store ? 'Employment document saved successfully' : 'Failed to save employment document'
        ]);
    }

    public function show($crewId): JsonResponse
    {
        $employmentDocument = EmploymentDocument::with('employmentDocumentType')->where('crew_id', $crewId)->get();

        return response()->json($employmentDocument);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'crew_id' => 'required',
            'employment_document_type_id' => 'required',
            'document_number' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp|max:5120', // 5MB max
        ]);

        $employmentDocument = EmploymentDocument::findOrFail($id);

        // Check if request is from crew (requires approval) or admin (direct update)
        $user = Auth::guard('sanctum')->user();
        $isCrew = $user && $user->is_crew == 1;

        if ($isCrew) {
            // Crew update: Create pending approval request
            // Verify crew owns this document
            if ($employmentDocument->crew_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Handle file upload if present
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('employment_documents_pending', 'public');
                $validated['file_path'] = $path;
                $validated['file_ext'] = $file->getClientOriginalExtension();
            }

            // Remove 'file' from validated data
            unset($validated['file']);

            // Create pending update
            $update = EmploymentDocumentUpdate::create([
                'employment_document_id' => $employmentDocument->id,
                'crew_id' => $user->id,
                'original_data' => $employmentDocument->only(['crew_id', 'employment_document_type_id', 'document_number', 'file_path', 'file_ext']),
                'updated_data' => $validated,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Update submitted for admin approval',
                'data' => $update
            ]);
        } else {
            // Admin update: Direct update without approval
            // Handle file upload if present
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('employment_documents', 'public');
                $validated['file_path'] = $path;
                $validated['file_ext'] = $file->getClientOriginalExtension();
            }

            // Remove 'file' from validated data before updating record
            unset($validated['file']);

            $updated = $employmentDocument->update($validated);

            return response()->json([
                'success' => $updated,
                'message' => $updated ? 'Employment document updated successfully' : 'Failed to update employment document'
            ]);
        }
    }

    public function destroy($id): JsonResponse
    {
        $employmentDocument = EmploymentDocument::findOrFail($id);
        $softDelete = $employmentDocument->delete();

        return response()->json([
            'success' => $softDelete,
            'message' => $softDelete ? 'Employment document deleted successfully' : 'Failed to deleted employment document'
        ]);
    }

    /**
     * View/download employment document file
     */
    public function viewFile($id): StreamedResponse|JsonResponse
    {
        $employmentDocument = EmploymentDocument::findOrFail($id);

        // Check if file exists
        if (!$employmentDocument->file_path || !Storage::disk('public')->exists($employmentDocument->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
        }

        $filePath = $employmentDocument->file_path;
        $mimeType = Storage::disk('public')->mimeType($filePath);

        // Return file for viewing in browser
        return response()->stream(function () use ($filePath) {
            $stream = Storage::disk('public')->readStream($filePath);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
        ]);
    }
}
