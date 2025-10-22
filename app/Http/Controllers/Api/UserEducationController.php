<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserEducationController extends Controller
{
    /**
     * Get all education records for a specific user
     */
    public function index($userId)
    {
        $user = User::findOrFail($userId);
        
        $educations = $user->educations()->get();

        return response()->json([
            'success' => true,
            'data' => $educations->map(function($education) {
                return [
                    'id' => $education->id,
                    'school_name' => $education->school_name,
                    'date_graduated' => $education->date_graduated,
                    'degree' => $education->degree,
                    'education_level' => $education->education_level,
                    'created_at' => $education->created_at,
                    'updated_at' => $education->updated_at,
                ];
            })
        ]);
    }

    /**
     * Store a new education record for a user
     */
    public function store(Request $request, $userId)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'date_graduated' => 'nullable|date',
            'degree' => 'nullable|string|max:255',
            'education_level' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($userId);

        $education = $user->educations()->create($request->only([
            'school_name',
            'date_graduated',
            'degree',
            'education_level',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Education record created successfully',
            'data' => [
                'id' => $education->id,
                'school_name' => $education->school_name,
                'date_graduated' => $education->date_graduated,
                'degree' => $education->degree,
                'education_level' => $education->education_level,
                'created_at' => $education->created_at,
                'updated_at' => $education->updated_at,
            ]
        ], 201);
    }

    /**
     * Get a specific education record
     */
    public function show($userId, $educationId)
    {
        $user = User::findOrFail($userId);
        $education = $user->educations()->findOrFail($educationId);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $education->id,
                'school_name' => $education->school_name,
                'date_graduated' => $education->date_graduated,
                'degree' => $education->degree,
                'education_level' => $education->education_level,
                'created_at' => $education->created_at,
                'updated_at' => $education->updated_at,
            ]
        ]);
    }

    /**
     * Update an education record
     */
    public function update(Request $request, $userId, $educationId)
    {
        $request->validate([
            'school_name' => 'sometimes|required|string|max:255',
            'date_graduated' => 'nullable|date',
            'degree' => 'nullable|string|max:255',
            'education_level' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($userId);
        $education = $user->educations()->findOrFail($educationId);

        $education->update($request->only([
            'school_name',
            'date_graduated',
            'degree',
            'education_level',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Education record updated successfully',
            'data' => [
                'id' => $education->id,
                'school_name' => $education->school_name,
                'date_graduated' => $education->date_graduated,
                'degree' => $education->degree,
                'education_level' => $education->education_level,
                'created_at' => $education->created_at,
                'updated_at' => $education->updated_at,
            ]
        ]);
    }

    /**
     * Delete an education record
     */
    public function destroy($userId, $educationId)
    {
        $user = User::findOrFail($userId);
        $education = $user->educations()->findOrFail($educationId);

        $education->delete();

        return response()->json([
            'success' => true,
            'message' => 'Education record deleted successfully'
        ]);
    }
}
