<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\FormatsUserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use FormatsUserData;
    public function getProfile($crewId, Request $request)
    {
        try {
            $currentUser = $request->user();

            // Find the user by crew_id in user_profiles table
            $user = User::whereHas('profile', function ($query) use ($crewId) {
                $query->where('crew_id', $crewId);
            })->with(['profile', 'contacts', 'employment', 'education', 'physicalTraits'])->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Check permissions: users can only view their own profile or admin can view any profile
            $currentUserCrewId = $currentUser->profile?->crew_id;
            if ($currentUserCrewId !== $crewId && $currentUser->is_crew !== 0) {
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

            // Get all crew members (is_crew = 1) with their related data
            $crew = User::where('is_crew', 1)
                ->with(['profile', 'contacts', 'employment.fleet', 'employment.rank', 'education', 'physicalTraits'])
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

            $user = User::with(['profile', 'contacts', 'employment.fleet', 'employment.rank', 'education', 'physicalTraits'])->find($id);

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

    public function getProfileAdmin($id, Request $request)
    {
        try {
            $currentUser = $request->user();

            // Check if user is admin
            if ($currentUser->is_crew === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to access this resource'
                ], 403);
            }

            // Find crew by id with related data
            $crew = User::where('id', $id)
                ->where('is_crew', 1)
                ->with(['profile', 'contacts', 'employment.fleet', 'employment.rank', 'education', 'physicalTraits'])
                ->first();

            if (!$crew) {
                return response()->json([
                    'success' => false,
                    'message' => 'Crew member not found'
                ], 404);
            }

            Log::info('Crew profile accessed by admin', [
                'admin_id' => $currentUser->id,
                'crew_id' => $id,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'crew' => $this->formatUserData($crew),
                'message' => 'Crew profile retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving crew profile for admin', [
                'crew_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the crew profile'
            ], 500);
        }
    }

}
