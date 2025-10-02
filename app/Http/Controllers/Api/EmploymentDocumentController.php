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
            'crew_id' => 'required|exists:users,id',
            'employment_document_type_id' => 'required|exists:employment_document_types,id',
            'document_number' => 'required|string|max:255',
        ]);

        $employmentDocument = EmploymentDocument::create($validated);
        $employmentDocument->load(['crew', 'employmentDocumentType']);

        return response()->json($employmentDocument, 201);
    }

    public function show(EmploymentDocument $employmentDocument): JsonResponse
    {
        $employmentDocument->load(['crew', 'employmentDocumentType']);

        return response()->json($employmentDocument);
    }

    public function update(Request $request, EmploymentDocument $employmentDocument): JsonResponse
    {
        $validated = $request->validate([
            'crew_id' => 'required|exists:users,id',
            'employment_document_type_id' => 'required|exists:employment_document_types,id',
            'document_number' => 'required|string|max:255',
        ]);

        $employmentDocument->update($validated);
        $employmentDocument->load(['crew', 'employmentDocumentType']);

        return response()->json($employmentDocument);
    }

    public function destroy(EmploymentDocument $employmentDocument): JsonResponse
    {
        $employmentDocument->delete();

        return response()->json(['message' => 'Employment document deleted successfully']);
    }
}
