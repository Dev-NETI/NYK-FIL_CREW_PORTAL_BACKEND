<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    /**
     * Display a listing of certificates.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Certificate::with('certificateType');

        // Filter by certificate type
        if ($request->has('certificate_type_id')) {
            $query->where('certificate_type_id', $request->certificate_type_id);
        }

        // Filter by regulation (STCW, Government, etc.)
        if ($request->has('regulation')) {
            $query->where('regulation', $request->regulation);
        }

        // Filter by STCW type (COC or COP)
        if ($request->has('stcw_type')) {
            $query->where('stcw_type', $request->stcw_type);
        }

        // Filter by NMC type
        if ($request->has('nmc_type')) {
            $query->where('nmc_type', $request->nmc_type);
        }

        // Filter by NMC department
        if ($request->has('nmc_department')) {
            $query->where('nmc_department', $request->nmc_department);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $certificates = $query->get();

        return response()->json($certificates);
    }

    /**
     * Store a newly created certificate.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'certificate_type_id' => 'required|exists:certificate_types,id',
            'regulation' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'stcw_type' => 'nullable|in:COC,COP',
            'code' => 'nullable|string|max:255',
            'vessel_type' => 'nullable|string|max:255',
            'nmc_type' => 'nullable|in:NMC,NMCR',
            'nmc_department' => 'nullable|in:Deck,Engine,Catering,Common',
            'rank' => 'nullable|string|max:255',
        ]);

        $certificate = Certificate::create($validated);
        $certificate->load('certificateType');

        return response()->json($certificate, 201);
    }

    /**
     * Display the specified certificate.
     */
    public function show($certificateTypeId): JsonResponse
    {
        $certificate = Certificate::with(['certificateType'])->where('certificate_type_id', $certificateTypeId)->get();
        return response()->json($certificate);
    }

    /**
     * Update the specified certificate.
     */
    public function update(Request $request, Certificate $certificate): JsonResponse
    {
        $validated = $request->validate([
            'certificate_type_id' => 'required|exists:certificate_types,id',
            'regulation' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'stcw_type' => 'nullable|in:COC,COP',
            'code' => 'nullable|string|max:255',
            'vessel_type' => 'nullable|string|max:255',
            'nmc_type' => 'nullable|in:NMC,NMCR',
            'nmc_department' => 'nullable|in:Deck,Engine,Catering,Common',
            'rank' => 'nullable|string|max:255',
        ]);

        $certificate->update($validated);
        $certificate->load('certificateType');

        return response()->json($certificate);
    }

    /**
     * Remove the specified certificate.
     */
    public function destroy(Certificate $certificate): JsonResponse
    {
        $certificate->delete();

        return response()->json(['message' => 'Certificate deleted successfully']);
    }

    /**
     * Get certificates by type.
     */
    public function getByType(int $certificateTypeId): JsonResponse
    {
        $certificates = Certificate::with('certificateType')
            ->where('certificate_type_id', $certificateTypeId)
            ->get();

        return response()->json($certificates);
    }

    /**
     * Get STCW certificates only (COC or COP).
     */
    public function getStcwCertificates(): JsonResponse
    {
        $certificates = Certificate::with('certificateType')
            ->whereIn('stcw_type', ['COC', 'COP'])
            ->get();

        return response()->json($certificates);
    }

    /**
     * Get NMC certificates by department.
     */
    public function getNmcCertificates(Request $request): JsonResponse
    {
        $query = Certificate::with('certificateType')
            ->whereNotNull('nmc_type');

        if ($request->has('department')) {
            $query->where('nmc_department', $request->department);
        }

        $certificates = $query->get();

        return response()->json($certificates);
    }
}
