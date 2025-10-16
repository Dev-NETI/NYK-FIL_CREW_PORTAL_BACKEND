<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminRoleController extends Controller
{
    /**
     * Display a listing of admin roles.
     */
    public function index()
    {
        $adminRoles = AdminRole::with(['user', 'role'])->get();

        return response()->json($adminRoles);
    }

    /**
     * Store a newly created admin role assignment.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'role_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            // Check if the role assignment already exists
            $existingRole = AdminRole::where('user_id', $request->user_id)
                ->where('role_id', $request->role_id)
                ->first();

            if ($existingRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'This role is already assigned to the user'
                ], 409);
            }

            $store = AdminRole::create([
                'user_id' => $request->user_id,
                'role_id' => $request->role_id,
            ]);

            return response()->json([
                'success' => $store ? true : false,
                'message' => $store ? 'Role assigned successfully' : 'Failed to assign role'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    /**
     * Display admin roles for a specific user.
     */
    public function getByUserId($userId)
    {
        $adminRoles = AdminRole::with(['role'])
            ->where('user_id', $userId)
            ->get();

        if ($adminRoles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No roles found for this user',
            ], 404);
        }

        return response()->json($adminRoles);
    }

    /**
     * Remove the specified admin role assignment.
     */
    public function destroy($id)
    {
        $adminRole = AdminRole::find($id);

        if (!$adminRole) {
            return response()->json([
                'success' => false,
                'message' => 'Admin role assignment not found'
            ], 404);
        }

        $destroy = $adminRole->delete();

        return response()->json([
            'success' => $destroy,
            'message' => $destroy ? 'Role deleted successfully' : 'Failed to deleted role'
        ]);
    }
}
