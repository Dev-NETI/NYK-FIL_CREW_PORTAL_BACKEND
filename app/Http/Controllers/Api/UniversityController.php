<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\University;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    public function index(): JsonResponse
    {
        $universities = University::with(['users'])->get();

        return response()->json($universities);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $university = University::create($validated);
        $university->load(['users']);

        return response()->json($university, 201);
    }

    public function show(University $university): JsonResponse
    {
        $university->load(['users']);

        return response()->json($university);
    }

    public function update(Request $request, University $university): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $university->update($validated);
        $university->load(['users']);

        return response()->json($university);
    }

    public function destroy(University $university): JsonResponse
    {
        $university->delete();

        return response()->json(['message' => 'University deleted successfully']);
    }
}
