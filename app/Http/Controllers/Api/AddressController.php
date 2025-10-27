<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Barangay;
use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{
    /**
     * Display a listing of addresses for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $addresses = Address::where('user_id', Auth::id())
            ->with(['region', 'province', 'city'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }

    /**
     * Store a newly created address.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'nullable|integer|exists:users,id',
                'full_address' => 'nullable',
                'street_address' => 'nullable|string|max:500',
                'brgy_id' => 'required|string|max:50',
                'city_id' => 'required|string|max:50',
                'province_id' => 'required|string|max:50',
                'region_id' => 'required|string|max:50',
                'zip_code' => 'nullable|string|max:10',
            ]);

            // Get the descriptions for building full address
            $region = Region::where('reg_code', $validated['region_id'])->first();
            $province = Province::where('prov_code', $validated['province_id'])->first();
            $city = City::where('citymun_code', $validated['city_id'])->first();
            $barangay = Barangay::where('brgy_code', $validated['brgy_id'])->first();

            // Build full address string
            $fullAddressParts = array_filter([
                $validated['street_address'],
                $barangay ? $barangay->brgy_desc : null,
                $city ? $city->citymun_desc : null,
                $province ? $province->prov_desc : null,
                $region ? $region->reg_desc : null,
                $validated['zip_code']
            ]);
            $fullAddress = strtoupper(implode(', ', $fullAddressParts));

            $address = Address::create([
                'user_id' => $validated['user_id'] ?? Auth::id(),
                'full_address' => $fullAddress,
                'street_address' => $validated['street_address'],
                'brgy_id' => $validated['brgy_id'],
                'city_id' => $validated['city_id'],
                'province_id' => $validated['province_id'],
                'region_id' => $validated['region_id'],
                'zip_code' => $validated['zip_code'],
            ]);

            return response()->json([
                'success' => true,
                'data' => $address,
                'message' => 'Address created successfully'
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified address.
     */
    public function show(Address $address): JsonResponse
    {
        // Ensure user can only view their own addresses unless they're admin
        if ($address->user_id !== Auth::id() && Auth::user()->is_crew) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }

        $address->load(['region', 'province', 'city']);

        return response()->json([
            'success' => true,
            'data' => $address
        ]);
    }

    /**
     * Update the specified address.
     */
    public function update(Request $request, Address $address): JsonResponse
    {
        // Ensure user can only update their own addresses unless they're admin
        if ($address->user_id !== Auth::id() && Auth::user()->is_crew) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'user_id' => 'nullable|integer|exists:users,id',
                'street_address' => 'nullable|string|max:500',
                'brgy_id' => 'required|string|max:50',
                'city_id' => 'required|string|max:50',
                'province_id' => 'required|string|max:50',
                'region_id' => 'required|string|max:50',
                'zip_code' => 'nullable|string|max:10',
            ]);

            // Get the descriptions for building full address
            $region = Region::where('reg_code', $validated['region_id'])->first();
            $province = Province::where('prov_code', $validated['province_id'])->first();
            $city = City::where('citymun_code', $validated['city_id'])->first();
            $barangay = Barangay::where('brgy_code', $validated['brgy_id'])->first();

            // Build full address string
            $fullAddressParts = array_filter([
                $validated['street_address'],
                $barangay ? $barangay->brgy_desc : null,
                $city ? $city->citymun_desc : null,
                $province ? $province->prov_desc : null,
                $region ? $region->reg_desc : null,
                $validated['zip_code']
            ]);
            $fullAddress = strtoupper(implode(', ', $fullAddressParts));

            // Add full_address to the validated data
            $validated['full_address'] = $fullAddress;

            // Only update user_id if provided, otherwise keep existing
            if (isset($validated['user_id'])) {
                $address->update($validated);
            } else {
                $address->update(array_diff_key($validated, ['user_id' => '']));
            }
            $address->load(['region', 'province', 'city']);

            return response()->json([
                'success' => true,
                'data' => $address,
                'message' => 'Address updated successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified address.
     */
    public function destroy(Address $address): JsonResponse
    {
        // Ensure user can only delete their own addresses unless they're admin
        if ($address->user_id !== Auth::id() && Auth::user()->is_crew) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully'
        ]);
    }
}
