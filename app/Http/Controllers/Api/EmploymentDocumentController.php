<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmploymentDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        ]);

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
        ]);

        $employmentDocument = EmploymentDocument::findOrFail($id);
        $updated = $employmentDocument->update($validated);

        return response()->json([
            'success' => $updated,
            'message' => $updated ? 'Employment document updated successfully' : 'Failed to update employment document'
        ]);
    }

    public function destroy(EmploymentDocument $employmentDocument): JsonResponse
    {
        $employmentDocument->delete();

        return response()->json(['message' => 'Employment document deleted successfully']);
    }
}
