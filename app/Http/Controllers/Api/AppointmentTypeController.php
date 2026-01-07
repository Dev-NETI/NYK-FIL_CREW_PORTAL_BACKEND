<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppointmentType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AppointmentTypeController extends Controller
{
    public function getByDepartment($departmentId): JsonResponse
    {
        $types = AppointmentType::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => $types]);
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();

        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $types = AppointmentType::where('department_id', $user->department_id)
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => $types]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if ($user->is_crew) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ]);

            $type = AppointmentType::create([
                'department_id' => $user->department_id,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $type,
                'message' => 'Appointment type created'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();

        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $type = AppointmentType::where('id', $id)
            ->where('department_id', $user->department_id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $type->fill($validated);
        $type->modified_by = $user->id;
        $type->save();

        return response()->json([
            'success' => true,
            'data' => $type,
            'message' => 'Appointment type updated'
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $user = Auth::user();

        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $type = AppointmentType::where('id', $id)
            ->where('department_id', $user->department_id)
            ->firstOrFail();

        $type->delete();

        return response()->json(['success' => true, 'message' => 'Appointment type deleted']);
    }

    public function toggle($id): JsonResponse
    {
        $user = Auth::user();

        if ($user->is_crew) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $type = AppointmentType::where('id', $id)
            ->where('department_id', $user->department_id)
            ->firstOrFail();

        $type->is_active = ! $type->is_active;
        $type->modified_by = $user->id;
        $type->save();

        return response()->json([
            'success' => true,
            'data' => $type,
            'message' => 'Appointment type status updated'
        ]);
    }

}
