<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index(): JsonResponse
    {
        $regions = Region::with(['island', 'provinces.cities'])->get();

        return response()->json($regions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'island_id' => 'required|exists:islands,id',
            'name' => 'required|string|max:255',
        ]);

        $region = Region::create($validated);
        $region->load(['island', 'provinces.cities']);

        return response()->json($region, 201);
    }

    public function show(Region $region): JsonResponse
    {
        $region->load(['island', 'provinces.cities']);

        return response()->json($region);
    }

    public function update(Request $request, Region $region): JsonResponse
    {
        $validated = $request->validate([
            'island_id' => 'required|exists:islands,id',
            'name' => 'required|string|max:255',
        ]);

        $region->update($validated);
        $region->load(['island', 'provinces.cities']);

        return response()->json($region);
    }

    public function destroy(Region $region): JsonResponse
    {
        $region->delete();

        return response()->json(['message' => 'Region deleted successfully']);
    }
}
