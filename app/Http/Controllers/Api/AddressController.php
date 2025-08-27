<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(): JsonResponse
    {
        $addresses = Address::with(['island', 'region', 'province', 'city', 'users', 'allotees'])->get();

        return response()->json($addresses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'street_address' => 'required|string',
            'island_id' => 'required|exists:islands,id',
            'region_id' => 'required|exists:regions,id',
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:cities,id',
        ]);

        $address = Address::create($validated);
        $address->load(['island', 'region', 'province', 'city', 'users', 'allotees']);

        return response()->json($address, 201);
    }

    public function show(Address $address): JsonResponse
    {
        $address->load(['island', 'region', 'province', 'city', 'users', 'allotees']);

        return response()->json($address);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        $validated = $request->validate([
            'street_address' => 'required|string',
            'island_id' => 'required|exists:islands,id',
            'region_id' => 'required|exists:regions,id',
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:cities,id',
        ]);

        $address->update($validated);
        $address->load(['island', 'region', 'province', 'city', 'users', 'allotees']);

        return response()->json($address);
    }

    public function destroy(Address $address): JsonResponse
    {
        $address->delete();

        return response()->json(['message' => 'Address deleted successfully']);
    }
}
