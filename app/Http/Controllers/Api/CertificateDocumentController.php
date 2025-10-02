<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CertificateDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CertificateDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $certificateDocuments = CertificateDocument::with(['crew', 'certificateDocumentType'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($certificateDocuments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'crew_id' => 'required|exists:user_profiles,id',
            'certificate_document_type_id' => 'required|exists:certificate_document_types,id',
            'certificate' => 'nullable|string|max:255',
            'certificate_no' => 'nullable|string|max:255',
            'issuing_authority' => 'nullable|string|max:255',
            'date_issued' => 'nullable|date',
            'expiry_date' => 'nullable|date',
        ]);

        $certificateDocument = CertificateDocument::create($validated);

        return response()->json($certificateDocument->load(['crew', 'certificateDocumentType']), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(CertificateDocument $certificateDocument)
    {
        return response()->json($certificateDocument->load(['crew', 'certificateDocumentType']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CertificateDocument $certificateDocument)
    {
        $validated = $request->validate([
            'crew_id' => 'sometimes|required|exists:user_profiles,id',
            'certificate_document_type_id' => 'sometimes|required|exists:certificate_document_types,id',
            'certificate' => 'nullable|string|max:255',
            'certificate_no' => 'nullable|string|max:255',
            'issuing_authority' => 'nullable|string|max:255',
            'date_issued' => 'nullable|date',
            'expiry_date' => 'nullable|date',
        ]);

        $certificateDocument->update($validated);

        return response()->json($certificateDocument->load(['crew', 'certificateDocumentType']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CertificateDocument $certificateDocument)
    {
        $certificateDocument->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
