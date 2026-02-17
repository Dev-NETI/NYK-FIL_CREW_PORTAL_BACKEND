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

    private function expandDates(array $validated): array
    {
        if (! empty($validated['dates']) && is_array($validated['dates'])) {
            return collect($validated['dates'])
                ->map(fn ($d) => Carbon::parse($d)->toDateString())
                ->unique()
                ->values()
                ->all();
        }

        if (! empty($validated['start_date']) && ! empty($validated['end_date'])) {
            $start = Carbon::parse($validated['start_date'])->startOfDay();
            $end = Carbon::parse($validated['end_date'])->startOfDay();

            if ($end->lt($start)) {
                throw ValidationException::withMessages([
                    'end_date' => 'End date must be the same as or later than start date.',
                ]);
            }

            $dates = [];
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $dates[] = $d->toDateString();
            }

            return $dates;
        }

        if (! empty($validated['date'])) {
            return [Carbon::parse($validated['date'])->toDateString()];
        }

        throw ValidationException::withMessages([
            'date' => 'Please provide either date, dates[], or start_date and end_date.',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($user->is_crew) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'total_slots' => 'required|integer|min:0',

                // Option A: single day
                'date' => 'nullable|date',

                // Option B: multiple days
                'dates' => 'nullable|array|min:1',
                'dates.*' => 'date',

                // Option C: date range
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
            ]);

            $dates = $this->expandDates($validated);

            $saved = [];
            foreach ($dates as $date) {
                $saved[] = DepartmentSchedule::updateOrCreate(
                    ['department_id' => $user->department_id, 'date' => $date],
                    [
                        'department_id' => $user->department_id,
                        'date' => $date,
                        'total_slots' => $validated['total_slots'],
                        'created_by' => Auth::id(),
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'data' => $saved,
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
                'total_slots' => 'required|integer|min:0',
            ]);

            $schedule->total_slots = $validated['total_slots'];
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
