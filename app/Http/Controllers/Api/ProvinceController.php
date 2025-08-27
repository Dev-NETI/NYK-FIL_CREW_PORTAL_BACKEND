<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    public function index(): JsonResponse
    {
        $provinces = Province::with(['region.island', 'cities'])->get();

        return response()->json($provinces);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|max:255',
        ]);

        $province = Province::create($validated);
        $province->load(['region.island', 'cities']);

        return response()->json($province, 201);
    }

    public function show(Province $province): JsonResponse
    {
        $province->load(['region.island', 'cities']);

        return response()->json($province);
    }

    public function update(Request $request, Province $province): JsonResponse
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|max:255',
        ]);

        $province->update($validated);
        $province->load(['region.island', 'cities']);

        return response()->json($province);
    }

    public function destroy(Province $province): JsonResponse
    {
        $province->delete();

        return response()->json(['message' => 'Province deleted successfully']);
    }
}
