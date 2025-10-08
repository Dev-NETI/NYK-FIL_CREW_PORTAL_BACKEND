<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TravelDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        ]);

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
        ]);

        $travelDocument = TravelDocument::findOrFail($id);
        $update = $travelDocument->update($validated);

        return response()->json([
            'success' => $update,
            'message' => $update ? 'Travel document updated successfully' : 'Failed to update travel document'
        ]);
    }

    public function destroy(TravelDocument $travelDocument): JsonResponse
    {
        $travelDocument->delete();

        return response()->json(['message' => 'Travel document deleted successfully']);
    }
}
