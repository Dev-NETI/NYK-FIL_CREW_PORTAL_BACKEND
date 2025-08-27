<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Island;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IslandController extends Controller
{
    public function index(): JsonResponse
    {
        $islands = Island::with(['regions.provinces.cities'])->get();

        return response()->json($islands);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $island = Island::create($validated);
        $island->load(['regions.provinces.cities']);

        return response()->json($island, 201);
    }

    public function show(Island $island): JsonResponse
    {
        $island->load(['regions.provinces.cities']);

        return response()->json($island);
    }

    public function update(Request $request, Island $island): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $island->update($validated);
        $island->load(['regions.provinces.cities']);

        return response()->json($island);
    }

    public function destroy(Island $island): JsonResponse
    {
        $island->delete();

        return response()->json(['message' => 'Island deleted successfully']);
    }
}
