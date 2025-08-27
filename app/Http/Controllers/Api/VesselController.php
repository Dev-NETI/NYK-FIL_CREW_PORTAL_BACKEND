<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vessel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VesselController extends Controller
{
    public function index(): JsonResponse
    {
        $vessels = Vessel::with(['vesselType', 'contracts.user'])->get();

        return response()->json($vessels);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vessel_id' => 'nullable|string|max:255',
            'vessel_type_id' => 'required|exists:vessel_types,id',
        ]);

        $vessel = Vessel::create($validated);
        $vessel->load(['vesselType', 'contracts.user']);

        return response()->json($vessel, 201);
    }

    public function show(Vessel $vessel): JsonResponse
    {
        $vessel->load(['vesselType', 'contracts.user']);

        return response()->json($vessel);
    }

    public function update(Request $request, Vessel $vessel): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vessel_id' => 'nullable|string|max:255',
            'vessel_type_id' => 'required|exists:vessel_types,id',
        ]);

        $vessel->update($validated);
        $vessel->load(['vesselType', 'contracts.user']);

        return response()->json($vessel);
    }

    public function destroy(Vessel $vessel): JsonResponse
    {
        $vessel->delete();

        return response()->json(['message' => 'Vessel deleted successfully']);
    }
}
