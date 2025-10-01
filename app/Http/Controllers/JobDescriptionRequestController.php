<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobDescriptionRequestRequest;
use App\Models\JobDescriptionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobDescriptionRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = JobDescriptionRequest::with(['crew', 'processedBy', 'approvedBy']);

        if ($request->has('crew_id')) {
            $query->forCrew($request->crew_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJobDescriptionRequestRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // For now, get the user with crew_id 219454 as specified
            $user = \App\Models\User::where('crew_id', '219454')->first();
            if (!$user) {
                throw new \Exception('Crew member not found');
            }
            $validated['crew_id'] = $user->id;
            $validated['status'] = 'pending';

            $jobRequest = JobDescriptionRequest::create($validated);

            // Load the crew relationship
            $jobRequest->load('crew');

            return response()->json([
                'success' => true,
                'message' => 'Job description request submitted successfully.',
                'data' => $jobRequest,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit job description request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $jobRequest = JobDescriptionRequest::with(['crew', 'processedBy', 'approvedBy'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $jobRequest,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Job description request not found.',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $jobRequest = JobDescriptionRequest::findOrFail($id);

            // Only allow updates from authorized users or specific status changes
            $allowedFields = [
                'status',
                'notes',
                'processed_by',
                'processed_date',
                'approved_by',
                'approved_date',
                'disapproval_reason',
                'vp_comments',
                'signature_added',
                'memo_no'
            ];

            $updateData = $request->only($allowedFields);

            $jobRequest->update($updateData);
            $jobRequest->load(['crew', 'processedBy', 'approvedBy']);

            return response()->json([
                'success' => true,
                'message' => 'Job description request updated successfully.',
                'data' => $jobRequest,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update job description request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $jobRequest = JobDescriptionRequest::findOrFail($id);

            // Only allow deletion if status is pending
            if ($jobRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete job description request that has been processed.',
                ], 422);
            }

            $jobRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Job description request deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete job description request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
