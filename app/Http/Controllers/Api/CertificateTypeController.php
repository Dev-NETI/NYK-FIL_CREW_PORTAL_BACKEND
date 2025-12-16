<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CertificateType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateTypeController extends Controller
{
    /**
     * Display a listing of certificate types.
     */
    public function index(): JsonResponse
    {
        $certificateTypes = CertificateType::all();

        return response()->json($certificateTypes);
    }

    /**
     * Store a newly created certificate type.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:certificate_types,name',
        ]);

        $certificateType = CertificateType::create($validated);

        return response()->json($certificateType, 201);
    }

    /**
     * Display the specified certificate type.
     */
    public function show(CertificateType $certificateType): JsonResponse
    {
        return response()->json($certificateType);
    }

    /**
     * Update the specified certificate type.
     */
    public function update(Request $request, CertificateType $certificateType): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:certificate_types,name,' . $certificateType->id,
        ]);

        $certificateType->update($validated);

        return response()->json($certificateType);
    }

    /**
     * Remove the specified certificate type.
     */
    public function destroy(CertificateType $certificateType): JsonResponse
    {
        $certificateType->delete();

        return response()->json(['message' => 'Certificate type deleted successfully']);
    }
}
