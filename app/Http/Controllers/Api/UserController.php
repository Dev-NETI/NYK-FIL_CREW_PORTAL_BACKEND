<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function getProfile($crewId, Request $request)
    {
        try {
            $currentUser = $request->user();

            // Find the user by crew_id
            $user = User::where('crew_id', $crewId)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Check permissions: users can only view their own profile or admin can view any profile
            if ($currentUser->crew_id !== $crewId && $currentUser->is_crew !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view this profile'
                ], 403);
            }

            Log::info('Profile accessed', [
                'viewer_id' => $currentUser->id,
                'viewed_crew_id' => $crewId,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'user' => $this->formatUserData($user),
                'message' => 'Profile retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving user profile', [
                'crew_id' => $crewId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the profile'
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $currentUser = $request->user();
            
            // Log the current user for debugging
            Log::info('Crew list access attempt', [
                'user_id' => $currentUser->id,
                'is_crew' => $currentUser->is_crew,
                'email' => $currentUser->email
            ]);
            
            // Check if user is admin (temporarily disabled for debugging - REMOVE IN PRODUCTION)
            // if ($currentUser->is_crew !== 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'You are not authorized to access this resource. Admin access required.',
            //         'debug' => [
            //             'current_user_is_crew' => $currentUser->is_crew,
            //             'required_is_crew' => 0
            //         ]
            //     ], 403);
            // }

            // Get all crew members (is_crew = 1) with their related data
            $crew = User::where('is_crew', 1)
                ->with(['fleet', 'rank'])
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedCrew = $crew->map(function ($user) {
                return $this->formatUserData($user);
            });

            Log::info('Crew list accessed', [
                'admin_id' => $currentUser->id,
                'total_crew' => $crew->count(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'crew' => $formattedCrew,
                'total' => $crew->count(),
                'message' => 'Crew list retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving crew list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the crew list'
            ], 500);
        }
    }

    public function show($id, Request $request)
    {
        try {
            $currentUser = $request->user();
            
            // Check if user is admin
            if ($currentUser->is_crew !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to access this resource'
                ], 403);
            }

            $user = User::with(['fleet', 'rank'])->find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'user' => $this->formatUserData($user),
                'message' => 'User retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the user'
            ], 500);
        }
    }

    private function formatUserData($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'last_login_at' => $user->last_login_at,
            'is_crew' => $user->is_crew,
            'crew_id' => $user->crew_id,
            'fleet_name' => optional($user->fleet)->name,
            'rank_name' => optional($user->rank)->name,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
            'suffix' => $user->suffix,
            'date_of_birth' => $user->date_of_birth,
            'age' => $user->age,
            'gender' => $user->gender,
            'mobile_number' => $user->mobile_number,
            'permanent_address_id' => $user->permanent_address_id,
            'graduated_school_id' => $user->graduated_school_id,
            'date_graduated' => $user->date_graduated,
            'crew_status' => $user->crew_status,
            'hire_status' => $user->hire_status,
            'hire_date' => $user->hire_date,
            'passport_number' => $user->passport_number,
            'passport_expiry' => $user->passport_expiry,
            'seaman_book_number' => $user->seaman_book_number,
            'seaman_book_expiry' => $user->seaman_book_expiry,
            'primary_allotee_id' => $user->primary_allotee_id,
            'last_login_ip' => $user->last_login_ip,
            'role' => $user->is_crew ? 'crew' : 'admin',
        ];
    }
}
