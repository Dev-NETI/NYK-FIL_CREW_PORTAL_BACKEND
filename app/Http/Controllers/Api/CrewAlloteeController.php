<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CrewAllotee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CrewAlloteeController extends Controller
{
    public function index(): JsonResponse
    {
        $crewAllotees = CrewAllotee::with(['user', 'allotee'])->get();

        return response()->json($crewAllotees);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'allotee_id' => 'required|exists:allotees,id',
            'is_primary' => 'boolean',
            'is_emergency_contact' => 'boolean',
        ]);

        $crewAllotee = CrewAllotee::create($validated);
        $crewAllotee->load(['user', 'allotee']);

        return response()->json($crewAllotee, 201);
    }

    public function show(CrewAllotee $crewAllotee): JsonResponse
    {
        $crewAllotee->load(['user', 'allotee']);

        return response()->json($crewAllotee);
    }

    public function update(Request $request, CrewAllotee $crewAllotee): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'allotee_id' => 'required|exists:allotees,id',
            'is_primary' => 'boolean',
            'is_emergency_contact' => 'boolean',
        ]);

        $crewAllotee->update($validated);
        $crewAllotee->load(['user', 'allotee']);

        return response()->json($crewAllotee);
    }

    public function destroy(CrewAllotee $crewAllotee): JsonResponse
    {
        $crewAllotee->delete();

        return response()->json(['message' => 'Crew allotee relationship deleted successfully']);
    }
}
