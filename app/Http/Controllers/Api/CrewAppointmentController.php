<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentCancellation;
use App\Models\AppointmentType;
use App\Models\Department;
use App\Models\DepartmentSchedule;
use Carbon\Carbon;
use App\Mail\AppointmentCreatedMail;
use App\Mail\AppointmentCancelledMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CrewAppointmentController extends Controller
{
    /**
     * List all appointments of the authenticated crew member.
     */
    public function index(): JsonResponse
    {
        $appointments = Appointment::where('created_by', Auth::id())
            ->with(['department', 'type'])
            ->orderBy('date', 'desc')
            ->orderBy('time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $appointments,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'appointment_type_id' => 'required|exists:appointment_types,id',
            'appointment_date' => 'required|date',
            'time' => 'required|string',
            'purpose' => 'required|string|max:1000',
        ]);

        $crew = Auth::user();

        try {
            $appointment = DB::transaction(function () use ($validated, $crew) {
                $schedule = DepartmentSchedule::where('department_id', $validated['department_id'])
                    ->whereDate('date', $validated['appointment_date'])
                    ->lockForUpdate()
                    ->first();

                if (! $schedule) {
                    throw ValidationException::withMessages([
                        'date' => 'No schedule available for selected date.',
                    ]);
                }

                $slotTaken = Appointment::where('schedule_id', $schedule->id)
                    ->where('time', $validated['time'])
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->lockForUpdate()
                    ->exists();

                if ($slotTaken) {
                    throw ValidationException::withMessages([
                        'time' => 'This time slot was just booked by another user.',
                    ]);
                }

                return Appointment::create([
                    'user_id' => $crew->id,
                    'department_id' => $validated['department_id'],
                    'appointment_type_id' => $validated['appointment_type_id'],
                    'schedule_id' => $schedule->id,
                    'date' => $validated['appointment_date'],
                    'time' => $validated['time'],
                    'purpose' => $validated['purpose'],
                    'status' => 'pending',
                    'created_by' => $crew->id,
                    'created_by_type' => 'crew',
                ]);
            });

            $appointment->load(['type', 'department', 'user']);

            // Send to department admin user(s) (users table)
            $departmentEmails = User::query()
                ->where('is_crew', 0)
                ->where('department_id', $appointment->department_id)
                ->whereNotNull('email')
                ->pluck('email')
                ->unique()
                ->values()
                ->all();

            Log::info('Appointment created - department recipients', [
                'appointment_id' => $appointment->id,
                'department_id' => $appointment->department_id,
                'department_emails' => $departmentEmails,
            ]);

            if (! empty($departmentEmails)) {
                Mail::to($departmentEmails)->send(
                    new AppointmentCreatedMail($appointment, $appointment->user, $appointment->department)
                );
            }

            return response()->json([
                'success' => true,
                'data' => $appointment,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Show appointment details
     */
    public function show(Appointment $appointment): JsonResponse
    {
        if ($appointment->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $appointment->load(['department', 'type']),
        ]);
    }

    /**
     * Calendar availability per month
     */
    public function calendar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'month' => 'required|date_format:Y-m',
        ]);

        $monthStart = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $schedules = DepartmentSchedule::where('department_id', $validated['department_id'])
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->withCount([
                'appointments as booked_slots' => fn ($q) =>
                    $q->whereIn('status', ['pending', 'confirmed']),
                'appointments as cancelled_slots' => fn ($q) =>
                    $q->where('status', 'cancelled'),
            ])
            ->orderBy('date')
            ->get();

        $data = $schedules->map(function ($s) {
            $available = max(0, (int) $s->total_slots - (int) $s->booked_slots);

            return [
                'date' => $s->date->toDateString(),
                'total_slots' => (int) $s->total_slots,
                'booked_slots' => (int) $s->booked_slots,
                'cancelled_slots' => (int) $s->cancelled_slots,
                'available_slots' => $available,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Available slots for a date
     */
    public function slots(Request $request): JsonResponse
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'date' => 'required|date',
        ]);

        $schedule = DepartmentSchedule::where('department_id', $request->department_id)
            ->whereDate('date', $request->date)
            ->first();

        if (! $schedule || ! $schedule->opening_time || ! $schedule->closing_time) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $duration = $schedule->slot_duration_minutes ?? 30;

        $bookedTimes = Appointment::where('schedule_id', $schedule->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('time')
            ->map(fn ($t) => substr($t, 0, 5))
            ->toArray();

        $slots = [];
        $current = strtotime($schedule->opening_time);
        $end = strtotime($schedule->closing_time);

        while ($current < $end) {
            $time = date('H:i', $current);

            $slots[] = [
                'time' => $time,
                'isAvailable' => ! in_array($time, $bookedTimes),
            ];

            $current += $duration * 60;
        }

        return response()->json([
            'success' => true,
            'data' => $slots,
        ]);
    }

    /**
     * Active departments
     */
    public function departments(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Department::where('is_active', 1)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Cancel appointment (crew)
     */
    public function cancel(Request $request, Appointment $appointment): JsonResponse
    {
        $crew = Auth::user();

        if ($appointment->created_by !== $crew->id) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found.',
            ], 404);
        }

        $validated = $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($appointment, $crew, $validated) {
            $appointment->update(['status' => 'cancelled']);

            AppointmentCancellation::create([
                'appointment_id' => $appointment->id,
                'cancelled_by' => $crew->id,          // FK -> users.id
                'cancelled_by_type' => 'crew',
                'reason' => $validated['remarks'],
                'cancelled_at' => now(),
            ]);
        });

        $appointment->load(['type', 'department', 'user']);

        $cancellation = AppointmentCancellation::query()
            ->where('appointment_id', $appointment->id)
            ->latest('cancelled_at')
            ->first();

        $departmentEmails = User::query()
            ->where('is_crew', 0)
            ->where('department_id', $appointment->department_id)
            ->whereNotNull('email')
            ->pluck('email')
            ->unique()
            ->values()
            ->all();

        Log::info('Appointment cancelled by crew - department recipients', [
            'appointment_id' => $appointment->id,
            'department_id' => $appointment->department_id,
            'department_emails' => $departmentEmails,
            'cancellation_id' => $cancellation?->id,
        ]);

        if (! empty($departmentEmails) && $cancellation) {
            Mail::to($departmentEmails)->send(
                new AppointmentCancelledMail($appointment, $cancellation, $appointment->user, $appointment->department)
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled successfully.',
        ]);
    }
}
