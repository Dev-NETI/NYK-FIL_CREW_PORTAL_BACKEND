<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DepartmentSchedule;
use Carbon\Carbon;
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
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $schedules]);
    }

    /**
     * Compute the capacity of slots between opening and closing time given a time duration.
     * - If duration is null, defaults to 30 minutes..
     */
    private function computeMaxSlots(?string $opening, ?string $closing, ?int $durationMinutes): int
    {
        if (! $opening || ! $closing) {
            return 0;
        }

        $duration = $durationMinutes ?: 30;
        if ($duration <= 0) {
            return 0;
        }

        $open = Carbon::createFromFormat('H:i', $opening);
        $close = Carbon::createFromFormat('H:i', $closing);

        $totalMinutes = $close->diffInMinutes($open, false);
        if ($totalMinutes <= 0) {
            return 0;
        }

        return intdiv($totalMinutes, $duration);
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

            $maxSlots = $this->computeMaxSlots(
                $validated['opening_time'] ?? null,
                $validated['closing_time'] ?? null,
                $validated['slot_duration_minutes'] ?? null
            );

            if ($maxSlots > 0 && (int) $validated['total_slots'] > $maxSlots) {
                throw ValidationException::withMessages([
                    'total_slots' => "Daily capacity cannot exceed {$maxSlots} based on the selected time range and slot duration.",
                ]);
            }

            $data = array_merge($validated, [
                'department_id' => $user->department_id,
                'created_by' => Auth::id(),
            ]);

            $schedule = DepartmentSchedule::updateOrCreate(
                ['department_id' => $user->department_id, 'date' => $validated['date']],
                $data
            );

            return response()->json([
                'success' => true,
                'data' => $schedule,
                'message' => 'Schedule saved',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
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

            $opening = array_key_exists('opening_time', $validated) ? $validated['opening_time'] : $schedule->opening_time;
            $closing = array_key_exists('closing_time', $validated) ? $validated['closing_time'] : $schedule->closing_time;
            $duration = array_key_exists('slot_duration_minutes', $validated) ? $validated['slot_duration_minutes'] : $schedule->slot_duration_minutes;

            $maxSlots = $this->computeMaxSlots($opening, $closing, $duration);

            $totalSlots = array_key_exists('total_slots', $validated) ? $validated['total_slots'] : $schedule->total_slots;

            if ($maxSlots > 0 && (int) $totalSlots > $maxSlots) {
                throw ValidationException::withMessages([
                    'total_slots' => "Daily capacity cannot exceed {$maxSlots} based on the selected time range and slot duration.",
                ]);
            }

            if ($opening && $closing) {
                $open = Carbon::createFromFormat('H:i', $opening);
                $close = Carbon::createFromFormat('H:i', $closing);

                if ($close->diffInMinutes($open, false) <= 0) {
                    throw ValidationException::withMessages([
                        'closing_time' => 'Closing time must be later than opening time.',
                    ]);
                }
            }

            $schedule->fill($validated);
            $schedule->modified_by = Auth::id();
            $schedule->save();

            return response()->json([
                'success' => true,
                'data' => $schedule,
                'message' => 'Schedule updated',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
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
