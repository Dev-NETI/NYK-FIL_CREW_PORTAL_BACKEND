<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RankGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RankGroupController extends Controller
{
    public function index(): JsonResponse
    {
        $rankGroups = RankGroup::with(['rankCategory', 'ranks'])->get();

        return response()->json($rankGroups);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rank_category_id' => 'required|exists:rank_categories,id',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $rankGroup = RankGroup::create($validated);
        $rankGroup->load(['rankCategory', 'ranks']);

        return response()->json($rankGroup, 201);
    }

    public function show(RankGroup $rankGroup): JsonResponse
    {
        $rankGroup->load(['rankCategory', 'ranks']);

        return response()->json($rankGroup);
    }

    public function update(Request $request, RankGroup $rankGroup): JsonResponse
    {
        $validated = $request->validate([
            'rank_category_id' => 'required|exists:rank_categories,id',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $rankGroup->update($validated);
        $rankGroup->load(['rankCategory', 'ranks']);

        return response()->json($rankGroup);
    }

    public function destroy(RankGroup $rankGroup): JsonResponse
    {
        $rankGroup->delete();

        return response()->json(['message' => 'Rank group deleted successfully']);
    }
}
