<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeographyController extends Controller
{
    public function getRegions(): JsonResponse
    {
        $regions = Region::select('reg_code', 'reg_desc')
            ->orderBy('reg_desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $regions
        ]);
    }

    public function getProvincesByRegion(Request $request): JsonResponse
    {
        $regCode = $request->query('reg_code');

        if (!$regCode) {
            return response()->json([
                'success' => false,
                'message' => 'Region code is required'
            ], 400);
        }

        $provinces = Province::where('reg_code', $regCode)
            ->select('prov_code', 'prov_desc', 'reg_code')
            ->orderBy('prov_desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $provinces
        ]);
    }

    public function getCitiesByProvince(Request $request): JsonResponse
    {
        $provCode = $request->query('prov_code');

        if (!$provCode) {
            return response()->json([
                'success' => false,
                'message' => 'Province code is required'
            ], 400);
        }

        $cities = City::where('prov_code', $provCode)
            ->select('citymun_code', 'citymun_desc', 'prov_code', 'reg_code')
            ->orderBy('citymun_desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    public function getBarangaysByCity(Request $request): JsonResponse
    {
        $cityCode = $request->query('citymun_code');

        if (!$cityCode) {
            return response()->json([
                'success' => false,
                'message' => 'City code is required'
            ], 400);
        }

        $barangays = Barangay::where('citymun_code', $cityCode)
            ->select('brgy_code', 'brgy_desc', 'citymun_code', 'prov_code', 'reg_code')
            ->orderBy('brgy_desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $barangays
        ]);
    }

    public function getRegionByCode(string $regCode): JsonResponse
    {
        $region = Region::where('reg_code', $regCode)
            ->select('reg_code', 'reg_desc')
            ->first();

        if (!$region) {
            return response()->json([
                'success' => false,
                'message' => 'Region not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $region
        ]);
    }

    public function getProvinceByCode(string $provCode): JsonResponse
    {
        $province = Province::where('prov_code', $provCode)
            ->select('prov_code', 'prov_desc', 'reg_code')
            ->first();

        if (!$province) {
            return response()->json([
                'success' => false,
                'message' => 'Province not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $province
        ]);
    }

    public function getCityByCode(string $cityCode): JsonResponse
    {
        $city = City::where('citymun_code', $cityCode)
            ->select('citymun_code', 'citymun_desc', 'prov_code', 'reg_code')
            ->first();

        if (!$city) {
            return response()->json([
                'success' => false,
                'message' => 'City not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $city
        ]);
    }

    public function getBarangayByCode(string $brgyCode): JsonResponse
    {
        $barangay = Barangay::where('brgy_code', $brgyCode)
            ->select('brgy_code', 'brgy_desc', 'citymun_code', 'prov_code', 'reg_code')
            ->first();

        if (!$barangay) {
            return response()->json([
                'success' => false,
                'message' => 'Barangay not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $barangay
        ]);
    }
}