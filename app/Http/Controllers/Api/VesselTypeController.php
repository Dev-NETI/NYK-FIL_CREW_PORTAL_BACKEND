<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VesselType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VesselTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $vesselTypes = VesselType::with(['vessels'])->get();

        return response()->json($vesselTypes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $vesselType = VesselType::create($validated);
        $vesselType->load(['vessels']);

        return response()->json($vesselType, 201);
    }

    public function show(VesselType $vesselType): JsonResponse
    {
        $vesselType->load(['vessels']);

        return response()->json($vesselType);
    }

    public function update(Request $request, VesselType $vesselType): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $vesselType->update($validated);
        $vesselType->load(['vessels']);

        return response()->json($vesselType);
    }

    public function destroy(VesselType $vesselType): JsonResponse
    {
        $vesselType->delete();

        return response()->json(['message' => 'Vessel type deleted successfully']);
    }
}
