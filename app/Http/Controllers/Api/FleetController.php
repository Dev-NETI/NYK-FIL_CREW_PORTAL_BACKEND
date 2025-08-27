<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fleet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    public function index(): JsonResponse
    {
        $fleets = Fleet::with(['vessels'])->get();

        return response()->json($fleets);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $fleet = Fleet::create($validated);
        $fleet->load(['vessels']);

        return response()->json($fleet, 201);
    }

    public function show(Fleet $fleet): JsonResponse
    {
        $fleet->load(['vessels']);

        return response()->json($fleet);
    }

    public function update(Request $request, Fleet $fleet): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $fleet->update($validated);
        $fleet->load(['vessels']);

        return response()->json($fleet);
    }

    public function destroy(Fleet $fleet): JsonResponse
    {
        $fleet->delete();

        return response()->json(['message' => 'Fleet deleted successfully']);
    }
}
