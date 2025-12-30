<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DepartmentSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DepartmentScheduleController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::user();
        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $schedules = DepartmentSchedule::where('department_id', $user->department_id)
            ->orderBy('date', 'asc')
            ->get();

        return response()->json(['success' => true, 'data' => $schedules]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($user->is_crew) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'date' => 'required|date',
                'total_slots' => 'required|integer|min:0',
                'opening_time' => 'nullable|date_format:H:i',
                'closing_time' => 'nullable|date_format:H:i|after:opening_time',
                'slot_duration_minutes' => 'nullable|integer|min:5',
            ]);

            $data = array_merge($validated, [
                'department_id' => $user->department_id,
                'created_by' => Auth::id(),
            ]);

            $schedule = DepartmentSchedule::updateOrCreate(
                ['department_id' => $user->department_id, 'date' => $validated['date']],
                $data
            );

            return response()->json(['success' => true, 'data' => $schedule, 'message' => 'Schedule saved']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($user->is_crew) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $schedule = DepartmentSchedule::where('id', $id)
                ->where('department_id', $user->department_id)
                ->firstOrFail();

            $validated = $request->validate([
                'total_slots' => 'nullable|integer|min:0',
                'opening_time' => 'nullable|date_format:H:i',
                'closing_time' => 'nullable|date_format:H:i',
                'slot_duration_minutes' => 'nullable|integer|min:5',
            ]);

            $schedule->fill($validated);
            $schedule->modified_by = Auth::id();
            $schedule->save();

            return response()->json(['success' => true, 'data' => $schedule, 'message' => 'Schedule updated']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $schedule = DepartmentSchedule::where('id', $id)
            ->where('department_id', $user->department_id)
            ->firstOrFail();

        $schedule->delete();

        return response()->json(['success' => true, 'message' => 'Schedule deleted']);
    }
}
