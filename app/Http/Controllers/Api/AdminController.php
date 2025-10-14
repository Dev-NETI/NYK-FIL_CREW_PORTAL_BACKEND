<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display a listing of admin users.
     */
    public function index()
    {
        try {
            // Get all admin users (is_crew = 0) with their profile
            $admins = User::where('is_crew', 0)
                ->with(['adminProfile', 'department'])
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedAdmins = $admins->map(function ($user) {
                return [
                    'id' => $user->id,
                    'email' => $user->email,
                    'department_id' => $user->department_id,
                    'department' => $user->department ? [
                        'id' => $user->department->id,
                        'name' => $user->department->name ?? null,
                    ] : null,
                    'profile' => $user->adminProfile ? [
                        'firstname' => $user->adminProfile->firstname,
                        'middlename' => $user->adminProfile->middlename,
                        'lastname' => $user->adminProfile->lastname,
                        'full_name' => trim("{$user->adminProfile->firstname} {$user->adminProfile->middlename} {$user->adminProfile->lastname}"),
                    ] : null,
                    'email_verified_at' => $user->email_verified_at,
                    'last_login_at' => $user->last_login_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedAdmins,
                'total' => $admins->count(),
                'message' => 'Admin list retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving admin list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created admin user.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email|unique:users,email',
                'department_id' => 'required',
                'firstname' => 'required|string|max:255',
                'middlename' => 'nullable|string|max:255',
                'lastname' => 'required|string|max:255',
            ]);

            DB::beginTransaction();

            try {
                // Create the user record
                $user = User::create([
                    'email' => $validatedData['email'],
                    'department_id' => $validatedData['department_id'],
                    'is_crew' => 0,
                ]);

                // Create the admin profile
                AdminProfile::create([
                    'user_id' => $user->id,
                    'firstname' => $validatedData['firstname'],
                    'middlename' => $validatedData['middlename'] ?? null,
                    'lastname' => $validatedData['lastname'],
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Admin account created successfully'
                ], 201);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified admin user.
     */
    public function show($id)
    {
        try {
            $user = User::where('id', $id)
                ->where('is_crew', 0)
                ->with(['adminProfile', 'department'])
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin user not found'
                ], 404);
            }
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the admin user'
            ], 500);
        }
    }

    /**
     * Update the specified admin user.
     */
    public function update($id, Request $request)
    {
        try {
            $user = User::where('id', $id)
                ->where('is_crew', 0)
                ->with(['adminProfile', 'department'])
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin user not found'
                ], 404);
            }
            $validatedData = $request->validate([
                'email' => 'required|email',
                'department_id' => 'required',
                'firstname' => 'required|string|max:255',
                'middlename' => 'nullable|string|max:255',
                'lastname' => 'required|string|max:255',
            ]);

            DB::beginTransaction();

            try {
                // Update user data
                $userUpdates = [];
                if (isset($validatedData['email'])) {
                    $userUpdates['email'] = $validatedData['email'];
                }
                if (isset($validatedData['department_id'])) {
                    $userUpdates['department_id'] = $validatedData['department_id'];
                }
                if (!empty($userUpdates)) {
                    $user->update($userUpdates);
                }

                // Update admin profile data
                $profileUpdates = [];
                if (isset($validatedData['firstname'])) {
                    $profileUpdates['firstname'] = $validatedData['firstname'];
                }
                if (isset($validatedData['middlename'])) {
                    $profileUpdates['middlename'] = $validatedData['middlename'];
                }
                if (isset($validatedData['lastname'])) {
                    $profileUpdates['lastname'] = $validatedData['lastname'];
                }

                if (!empty($profileUpdates)) {

                    if ($user->adminProfile) {
                        $user->adminProfile->update($profileUpdates);
                    } else {
                        // Create profile if it doesn't exist
                        $user->adminProfile()->create(array_merge($profileUpdates, [
                            'user_id' => $user->id,
                            'firstname' => $validatedData['firstname'] ?? '',
                            'lastname' => $validatedData['lastname'] ?? '',
                        ]));
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Admin account updated successfully'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete the specified admin user.
     */
    public function destroy($id)
    {
        try {
            $user = User::where('id', $id)
                ->where('is_crew', 0)
                ->with('adminProfile')
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin user not found'
                ], 404);
            }

            DB::beginTransaction();

            try {
                // Soft delete the admin profile first
                if ($user->adminProfile) {
                    $user->adminProfile->delete();
                }

                // Soft delete the user
                $user->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Admin account deleted successfully'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
