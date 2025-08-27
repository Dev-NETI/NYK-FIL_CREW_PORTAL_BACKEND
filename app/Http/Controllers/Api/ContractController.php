<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index(): JsonResponse
    {
        $contracts = Contract::with(['user.rank', 'vessel.vesselType'])->get();

        return response()->json($contracts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contract_number' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'vessel_id' => 'required|exists:vessels,id',
            'departure_date' => 'nullable|date',
            'arrival_date' => 'nullable|date',
            'duration_months' => 'nullable|integer|min:1|max:120',
            'contract_start_date' => 'required|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
        ]);

        $contract = Contract::create($validated);
        $contract->load(['user.rank', 'vessel.vesselType']);

        return response()->json($contract, 201);
    }

    public function show(Contract $contract): JsonResponse
    {
        $contract->load(['user.rank', 'vessel.vesselType']);

        return response()->json($contract);
    }

    public function update(Request $request, Contract $contract): JsonResponse
    {
        $validated = $request->validate([
            'contract_number' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'vessel_id' => 'required|exists:vessels,id',
            'departure_date' => 'nullable|date',
            'arrival_date' => 'nullable|date',
            'duration_months' => 'nullable|integer|min:1|max:120',
            'contract_start_date' => 'required|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
        ]);

        $contract->update($validated);
        $contract->load(['user.rank', 'vessel.vesselType']);

        return response()->json($contract);
    }

    public function destroy(Contract $contract): JsonResponse
    {
        $contract->delete();

        return response()->json(['message' => 'Contract deleted successfully']);
    }
}
