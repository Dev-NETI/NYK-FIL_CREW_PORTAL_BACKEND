<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TravelDocument;
use App\Models\TravelDocumentUpdate;
use App\Traits\SendsDocumentNotifications;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TravelDocumentController extends Controller
{
    use SendsDocumentNotifications;
    public function index(): JsonResponse
    {
        $travelDocuments = TravelDocument::with(['userProfile', 'travelDocumentType'])->get();

        return response()->json($travelDocuments);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'crew_id' => 'required',
            'id_no' => 'required',
            'travel_document_type_id' => 'required',
            'place_of_issue' => 'required|string|max:255',
            'date_of_issue' => 'required|date',
            'expiration_date' => 'required|date|after:date_of_issue',
            'remaining_pages' => 'nullable|integer|min:0',
            'is_US_VISA' => 'required',
            'visa_type' => 'nullable',
            'file' => 'nullable|file|max:5120', // 5MB max
        ]);

        if (Auth::guard('sanctum')->user()->is_crew == 1) {
            // Crew creating new document: Create pending approval request
            // First, create a temporary travel document to hold the reference
            $tempData = [
                'crew_id' => $validated['crew_id'],
                'travel_document_type_id' => $validated['travel_document_type_id'],
                'id_no' => 'PENDING_' . time(), // Temporary placeholder
                'place_of_issue' => 'PENDING',
                'date_of_issue' => now(),
                'expiration_date' => now()->addYear(),
                'is_US_VISA' => $validated['is_US_VISA'],
            ];

            $travelDocument = TravelDocument::create($tempData);

            // Prepare data for approval
            $newData = [
                'travel_document_type_id' => $validated['travel_document_type_id'],
                'id_no' => $validated['id_no'],
                'place_of_issue' => $validated['place_of_issue'],
                'date_of_issue' => $validated['date_of_issue'],
                'expiration_date' => $validated['expiration_date'],
                'is_US_VISA' => $validated['is_US_VISA'],
            ];

            if (isset($validated['remaining_pages'])) {
                $newData['remaining_pages'] = $validated['remaining_pages'];
            }

            if (isset($validated['visa_type'])) {
                $newData['visa_type'] = $validated['visa_type'];
            }

            // Handle file upload if present
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('travel_documents_pending', 'public');
                $newData['file_path'] = $path;
                $newData['file_ext'] = $file->getClientOriginalExtension();
            }

            // Create pending update request
            $update = TravelDocumentUpdate::create([
                'travel_document_id' => $travelDocument->id,
                'crew_id' => $validated['crew_id'],
                'original_data' => $tempData,
                'updated_data' => $newData,
                'status' => 'pending',
            ]);

            // Load the relationship for the response
            $update->load('userProfile', 'travelDocument.travelDocumentType');

            // Send email notification to admin
            $this->sendAdminNotification(
                $update,
                'created',
                'Travel',
                $update->travelDocument?->travelDocumentType?->name ?? 'Travel Document',
                [
                    'ID Number' => $update->updated_data['id_no'] ?? 'N/A',
                    'Place of Issue' => $update->updated_data['place_of_issue'] ?? 'N/A',
                    'Date of Issue' => $update->updated_data['date_of_issue'] ?? 'N/A',
                    'Expiration Date' => $update->updated_data['expiration_date'] ?? 'N/A',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'New travel document submitted for admin approval',
                'data' => $update
            ]);
        } else {
            // Admin creating document: Direct creation without approval
            // Handle file upload if present
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('travel_documents', 'public');
                $validated['file_path'] = $path;
                $validated['file_ext'] = $file->getClientOriginalExtension();
            }

            // Remove 'file' from validated data before creating record
            unset($validated['file']);

            $store = TravelDocument::create($validated);

            return response()->json([
                'success' => $store ? true : false,
                'message' => $store ? 'Travel document saved successfully' : 'Failed to save travel document'
            ]);
        }
    }

    public function show($crewId): JsonResponse
    {
        $travelDocument = TravelDocument::with('travelDocumentType')->where('crew_id', $crewId)->get();

        return response()->json($travelDocument);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'crew_id' => 'nullable|string',
            'id_no' => 'required',
            'place_of_issue' => 'required|string|max:255',
            'date_of_issue' => 'required|date',
            'expiration_date' => 'required|date|after:date_of_issue',
            'remaining_pages' => 'nullable|integer|min:0',
            'is_US_VISA' => 'required',
            'visa_type' => 'nullable',
            'file' => 'nullable|file|max:5120', // 5MB max
        ]);

        $travelDocument = TravelDocument::findOrFail($id);

        if ($validated['crew_id']) {
            // Crew update: Create pending approval request
            $updatedData = [
                'travel_document_type_id' => $travelDocument->travel_document_type_id,
                'id_no' => $validated['id_no'],
                'place_of_issue' => $validated['place_of_issue'],
                'date_of_issue' => $validated['date_of_issue'],
                'expiration_date' => $validated['expiration_date'],
                'is_US_VISA' => $validated['is_US_VISA'],
            ];

            if (isset($validated['remaining_pages'])) {
                $updatedData['remaining_pages'] = $validated['remaining_pages'];
            }

            if (isset($validated['visa_type'])) {
                $updatedData['visa_type'] = $validated['visa_type'];
            }

            // Handle file upload if present
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('travel_documents_pending', 'public');
                $updatedData['file_path'] = $path;
                $updatedData['file_ext'] = $file->getClientOriginalExtension();
            }

            // Create pending update
            $update = TravelDocumentUpdate::create([
                'travel_document_id' => $travelDocument->id,
                'crew_id' => $validated['crew_id'],
                'original_data' => $travelDocument->only(['crew_id', 'travel_document_type_id', 'id_no', 'place_of_issue', 'date_of_issue', 'expiration_date', 'remaining_pages', 'visa_type', 'is_US_VISA', 'file_path', 'file_ext']),
                'updated_data' => $updatedData,
                'status' => 'pending',
            ]);

            // Load the relationship for the response
            $update->load('userProfile', 'travelDocument.travelDocumentType');

            // Send email notification to admin
            $this->sendAdminNotification(
                $update,
                'updated',
                'Travel',
                $update->travelDocument?->travelDocumentType?->name ?? 'Travel Document',
                [
                    'ID Number' => $update->updated_data['id_no'] ?? 'N/A',
                    'Place of Issue' => $update->updated_data['place_of_issue'] ?? 'N/A',
                    'Date of Issue' => $update->updated_data['date_of_issue'] ?? 'N/A',
                    'Expiration Date' => $update->updated_data['expiration_date'] ?? 'N/A',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Update submitted for admin approval',
                'data' => $update
            ]);
        } else {
            // Admin update: Direct update without approval
            $updateData = [
                'id_no' => $validated['id_no'],
                'place_of_issue' => $validated['place_of_issue'],
                'date_of_issue' => $validated['date_of_issue'],
                'expiration_date' => $validated['expiration_date'],
                'is_US_VISA' => $validated['is_US_VISA'],
            ];

            if (isset($validated['remaining_pages'])) {
                $updateData['remaining_pages'] = $validated['remaining_pages'];
            }

            if (isset($validated['visa_type'])) {
                $updateData['visa_type'] = $validated['visa_type'];
            }

            // Handle file upload if present
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('travel_documents', 'public');
                $updateData['file_path'] = $path;
                $updateData['file_ext'] = $file->getClientOriginalExtension();
            }

            $update = $travelDocument->update($updateData);

            return response()->json([
                'success' => $update,
                'message' => $update ? 'Travel document updated successfully' : 'Failed to update travel document'
            ]);
        }
    }

    public function destroy($id): JsonResponse
    {
        $travelDocument = TravelDocument::findOrFail($id);
        $softDelete = $travelDocument->delete();

        return response()->json([
            'success' => $softDelete,
            'message' => $softDelete ? 'Travel document deleted successfully' : 'Failed to delete travel document'
        ]);
    }

    /**
     * View/download travel document file
     */
    public function viewFile($id): StreamedResponse|JsonResponse
    {
        $travelDocument = TravelDocument::findOrFail($id);

        // Check if file exists
        if (!$travelDocument->file_path || !Storage::disk('public')->exists($travelDocument->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
        }

        $filePath = $travelDocument->file_path;
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
