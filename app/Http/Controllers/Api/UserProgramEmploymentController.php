<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserProgramEmployment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class UserProgramEmploymentController extends Controller
{
    /**
     * Get employment records for a specific user.
     */
    public function index($userId)
    {
        $user = User::findOrFail($userId);
        
        $employmentRecords = UserProgramEmployment::where('user_id', $userId)
            ->with('program')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $employmentRecords,
            'message' => 'Employment records retrieved successfully'
        ]);
    }

    /**
     * Store a new employment record.
     */
    public function store(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $validator = Validator::make($request->all(), [
            'program_id' => 'required|exists:programs,id',
            'batch' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $employmentRecord = UserProgramEmployment::create([
            'user_id' => $userId,
            ...$validator->validated()
        ]);

        $employmentRecord->load('program');

        return response()->json([
            'success' => true,
            'data' => $employmentRecord,
            'message' => 'Employment record created successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified employment record.
     */
    public function show($userId, UserProgramEmployment $employment)
    {
        // Ensure the employment record belongs to the specified user
        if ($employment->user_id != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Employment record not found for this user'
            ], Response::HTTP_NOT_FOUND);
        }

        $employment->load('program');

        return response()->json([
            'success' => true,
            'data' => $employment,
            'message' => 'Employment record retrieved successfully'
        ]);
    }

    /**
     * Update the specified employment record.
     */
    public function update(Request $request, $userId, UserProgramEmployment $employment)
    {
        // Ensure the employment record belongs to the specified user
        if ($employment->user_id != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Employment record not found for this user'
            ], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'program_id' => 'required|exists:programs,id',
            'batch' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $employment->update($validator->validated());
        $employment->load('program');

        return response()->json([
            'success' => true,
            'data' => $employment,
            'message' => 'Employment record updated successfully'
        ]);
    }

    /**
     * Remove the specified employment record.
     */
    public function destroy($userId, UserProgramEmployment $employment)
    {
        // Ensure the employment record belongs to the specified user
        if ($employment->user_id != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Employment record not found for this user'
            ], Response::HTTP_NOT_FOUND);
        }

        $employment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employment record deleted successfully'
        ]);
    }
}