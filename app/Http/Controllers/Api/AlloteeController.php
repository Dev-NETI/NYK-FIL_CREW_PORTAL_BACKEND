<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Allotee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlloteeController extends Controller
{
    public function index(): JsonResponse
    {
        $allotees = Allotee::with(['users', 'primaryFor'])->get();

        return response()->json($allotees);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'mobile_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
        ]);

        $allotee = Allotee::create($validated);
        $allotee->load(['users', 'primaryFor']);

        return response()->json($allotee, 201);
    }

    public function show(Allotee $allotee): JsonResponse
    {
        $allotee->load(['users', 'primaryFor']);

        return response()->json($allotee);
    }

    public function update(Request $request, Allotee $allotee): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'mobile_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
        ]);

        $allotee->update($validated);
        $allotee->load(['users', 'primaryFor']);

        return response()->json($allotee);
    }

    public function destroy(Allotee $allotee): JsonResponse
    {
        $allotee->delete();

        return response()->json(['message' => 'Allotee deleted successfully']);
    }
}
