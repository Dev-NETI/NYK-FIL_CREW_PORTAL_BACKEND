<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentCancellation;
use App\Models\Department;
use App\Models\DepartmentSchedule;
use Carbon\Carbon;
use App\Mail\AppointmentCreatedMail;
use App\Mail\AppointmentCancelledMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CrewAppointmentController extends Controller
{
    public function index(): JsonResponse
    {
        $crewId = Auth::id();

        Appointment::query()
            ->where('created_by', $crewId)
            ->where('status', 'confirmed')
            ->whereNotNull('qr_expires_at')
            ->where('qr_expires_at', '<', now())
            ->update([
                'status' => 'no show',
                'qr_token' => null,
            ]);

        $appointments = Appointment::where('created_by', $crewId)
            ->with(['department', 'type', 'cancellations'])
            ->orderBy('date', 'desc')
            ->orderByRaw("FIELD(session, 'AM', 'PM')")
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
            'session' => 'required|in:AM,PM',
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

                $confirmedCount = Appointment::where('schedule_id', $schedule->id)
                    ->where('status', 'confirmed')
                    ->lockForUpdate()
                    ->count();

                if ($confirmedCount >= (int) $schedule->total_slots) {
                    throw ValidationException::withMessages([
                        'date' => 'This date is fully booked.',
                    ]);
                }

                $alreadyBookedByCrew = Appointment::where('schedule_id', $schedule->id)
                    ->where('created_by', $crew->id)
                    ->where('session', $validated['session'])
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyBookedByCrew) {
                    throw ValidationException::withMessages([
                        'session' => 'You already have an appointment for this session on the selected date.',
                    ]);
                }

                return Appointment::create([
                    'user_id' => $crew->id,
                    'department_id' => $validated['department_id'],
                    'appointment_type_id' => $validated['appointment_type_id'],
                    'schedule_id' => $schedule->id,
                    'date' => $validated['appointment_date'],
                    'session' => $validated['session'],

                    // keep time null for new flow
                    'time' => null,

                    'purpose' => $validated['purpose'],
                    'status' => 'pending',
                    'created_by' => $crew->id,
                    'created_by_type' => 'crew',
                ]);
            });

            $appointment->load(['type', 'department', 'user']);

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
                'appointments as booked_slots' => fn ($q) => $q->where('status', 'confirmed'),
                'appointments as cancelled_slots' => fn ($q) => $q->where('status', 'cancelled'),
            ])
            ->orderBy('date')
            ->get();

        $data = $schedules->map(function ($s) {
            $available = max(0, (int) $s->total_slots - (int) $s->booked_slots);

            return [
                'date' => Carbon::parse($s->date)->toDateString(),
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

    public function slots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'date' => 'required|date',
        ]);

        $crew = Auth::user();

        $schedule = DepartmentSchedule::where('department_id', $validated['department_id'])
            ->whereDate('date', $validated['date'])
            ->first();

        if (! $schedule) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $existingSessions = Appointment::where('schedule_id', $schedule->id)
            ->where('created_by', $crew->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('session')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                ['value' => 'AM', 'isAvailable' => ! in_array('AM', $existingSessions, true)],
                ['value' => 'PM', 'isAvailable' => ! in_array('PM', $existingSessions, true)],
            ],
        ]);
    }

    public function departments(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Department::where('is_active', 1)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

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
            $appointment->update(['status' => 'cancelled', 'qr_token' => null]);

            AppointmentCancellation::create([
                'appointment_id' => $appointment->id,
                'cancelled_by' => $crew->id,
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

    public function qrToken(Appointment $appointment): JsonResponse
    {
        $userId = Auth::id();

        if ($appointment->created_by !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found.',
            ], 404);
        }

        if ($appointment->status !== 'confirmed') {
            if ($appointment->qr_token) {
                $appointment->update(['qr_token' => null]);
            }

            return response()->json([
                'success' => false,
                'message' => 'QR is available only for confirmed appointments.',
            ], 422);
        }

        $date = Carbon::parse($appointment->date)->format('Y-m-d');
        $cutoffTime = $appointment->session === 'AM' ? '12:00:00' : '23:59:59';
        $scheduledAt = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $cutoffTime);

        if (now()->greaterThan($scheduledAt)) {
            if ($appointment->status === 'confirmed') {
                $appointment->update([
                    'status' => 'no show',
                    'qr_token' => null,
                ]);
            } else if ($appointment->qr_token) {
                $appointment->update(['qr_token' => null]);
            }

            return response()->json([
                'success' => true,
                'data' => ['token' => null],
                'message' => 'QR not available for past appointments.',
            ], 200);
        }

        if (! $appointment->qr_token) {
            $appointment->update([
                'qr_token' => Str::random(64),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $appointment->qr_token,
            ],
        ]);
    }
}
