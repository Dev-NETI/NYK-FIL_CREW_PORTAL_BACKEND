<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RankCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RankCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $rankCategories = RankCategory::with(['rankGroups.ranks'])->get();

        return response()->json($rankCategories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $rankCategory = RankCategory::create($validated);
        $rankCategory->load(['rankGroups.ranks']);

        return response()->json($rankCategory, 201);
    }

    public function show(RankCategory $rankCategory): JsonResponse
    {
        $rankCategory->load(['rankGroups.ranks']);

        return response()->json($rankCategory);
    }

    public function update(Request $request, RankCategory $rankCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $rankCategory->update($validated);
        $rankCategory->load(['rankGroups.ranks']);

        return response()->json($rankCategory);
    }

    public function destroy(RankCategory $rankCategory): JsonResponse
    {
        $rankCategory->delete();

        return response()->json(['message' => 'Rank category deleted successfully']);
    }
}
