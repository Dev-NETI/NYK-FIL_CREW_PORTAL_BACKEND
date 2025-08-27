<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RankController extends Controller
{
    public function index(): JsonResponse
    {
        $ranks = Rank::with(['rankGroup.rankCategory'])->get();

        return response()->json($ranks);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rank_group_id' => 'required|exists:rank_groups,id',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $rank = Rank::create($validated);
        $rank->load(['rankGroup.rankCategory']);

        return response()->json($rank, 201);
    }

    public function show(Rank $rank): JsonResponse
    {
        $rank->load(['rankGroup.rankCategory']);

        return response()->json($rank);
    }

    public function update(Request $request, Rank $rank): JsonResponse
    {
        $validated = $request->validate([
            'rank_group_id' => 'required|exists:rank_groups,id',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $rank->update($validated);
        $rank->load(['rankGroup.rankCategory']);

        return response()->json($rank);
    }

    public function destroy(Rank $rank): JsonResponse
    {
        $rank->delete();

        return response()->json(['message' => 'Rank deleted successfully']);
    }
}
