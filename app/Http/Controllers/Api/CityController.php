<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(): JsonResponse
    {
        $cities = City::with(['province.region.island', 'addresses', 'universities'])->get();

        return response()->json($cities);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'name' => 'required|string|max:255',
        ]);

        $city = City::create($validated);
        $city->load(['province.region.island', 'addresses', 'universities']);

        return response()->json($city, 201);
    }

    public function show(City $city): JsonResponse
    {
        $city->load(['province.region.island', 'addresses', 'universities']);

        return response()->json($city);
    }

    public function update(Request $request, City $city): JsonResponse
    {
        $validated = $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'name' => 'required|string|max:255',
        ]);

        $city->update($validated);
        $city->load(['province.region.island', 'addresses', 'universities']);

        return response()->json($city);
    }

    public function destroy(City $city): JsonResponse
    {
        $city->delete();

        return response()->json(['message' => 'City deleted successfully']);
    }
}
