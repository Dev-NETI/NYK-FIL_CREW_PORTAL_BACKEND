<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TravelDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TravelDocumentController extends Controller
{
    public function index(): JsonResponse
    {
        $travelDocuments = TravelDocument::with(['crew', 'travelDocumentType'])->get();

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

    public function show($crewId): JsonResponse
    {
        $travelDocument = TravelDocument::with('travelDocumentType')->where('crew_id', $crewId)->get();

        return response()->json($travelDocument);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
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

        // Handle file upload if present
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('travel_documents', 'public');
            $validated['file_path'] = $path;
            $validated['file_ext'] = $file->getClientOriginalExtension();
        }

        // Remove 'file' from validated data before updating record
        unset($validated['file']);

        $update = $travelDocument->update($validated);

        return response()->json([
            'success' => $update,
            'message' => $update ? 'Travel document updated successfully' : 'Failed to update travel document'
        ]);
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
