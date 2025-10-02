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
            'crew_id' => 'required|exists:user_profiles,id',
            'id_no' => 'required|string|max:255',
            'travel_document_type_id' => 'required|exists:travel_document_types,id',
            'place_of_issue' => 'required|string|max:255',
            'date_of_issue' => 'required|date',
            'expiration_date' => 'required|date|after:date_of_issue',
            'remaining_pages' => 'nullable|integer|min:0',
        ]);

        $travelDocument = TravelDocument::create($validated);
        $travelDocument->load(['crew', 'travelDocumentType']);

        return response()->json($travelDocument, 201);
    }

    public function show(TravelDocument $travelDocument): JsonResponse
    {
        $travelDocument->load(['crew', 'travelDocumentType']);

        return response()->json($travelDocument);
    }

    public function update(Request $request, TravelDocument $travelDocument): JsonResponse
    {
        $validated = $request->validate([
            'crew_id' => 'required|exists:user_profiles,id',
            'id_no' => 'required|string|max:255',
            'travel_document_type_id' => 'required|exists:travel_document_types,id',
            'place_of_issue' => 'required|string|max:255',
            'date_of_issue' => 'required|date',
            'expiration_date' => 'required|date|after:date_of_issue',
            'remaining_pages' => 'nullable|integer|min:0',
        ]);

        $travelDocument->update($validated);
        $travelDocument->load(['crew', 'travelDocumentType']);

        return response()->json($travelDocument);
    }

    public function destroy(TravelDocument $travelDocument): JsonResponse
    {
        $travelDocument->delete();

        return response()->json(['message' => 'Travel document deleted successfully']);
    }
}
