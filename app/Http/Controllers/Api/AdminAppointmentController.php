<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentCancellation;
use App\Models\DepartmentSchedule;
use Carbon\Carbon;
use App\Mail\AppointmentConfirmedMail;
use App\Mail\AppointmentCancelledMail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminAppointmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $query = Appointment::with(['type', 'user.profile', 'cancellations'])
            ->where('department_id', $user->department_id);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->string('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->string('to'));
        }

        return response()->json([
            'success' => true,
            'data' => $query
                ->orderBy('date', 'desc')
                ->orderByRaw("FIELD(session, 'AM', 'PM')")
                ->get(),
        ]);
    }

    public function calendar(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $start = Carbon::createFromFormat('Y-m', $request->string('month'))->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $schedules = DepartmentSchedule::query()
            ->where('department_id', $user->department_id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->withCount([
                'appointments as booked_slots' => fn ($q) => $q->where('status', 'confirmed'),
                'appointments as cancelled_slots' => fn ($q) => $q->where('status', 'cancelled'),
            ])
            ->get();

        $data = $schedules->map(static function ($s) {
            $totalSlots = (int) $s->total_slots;
            $bookedSlots = (int) $s->booked_slots;

            return [
                'date' => Carbon::parse($s->date)->toDateString(),
                'total_slots' => $totalSlots,
                'booked_slots' => $bookedSlots,
                'cancelled_slots' => (int) $s->cancelled_slots,
                'available_slots' => max(0, $totalSlots - $bookedSlots),
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function show(int $id): JsonResponse
    {
        $user = Auth::user();

        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $appointment = Appointment::query()
            ->where('id', $id)
            ->where('department_id', $user->department_id)
            ->with(['type', 'user.profile', 'cancellations'])
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $appointment]);
    }

    public function confirm(int $id): JsonResponse
    {
        $admin = Auth::user();

        if ($admin->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $appointment = DB::transaction(function () use ($id, $admin) {
                $appointment = Appointment::query()
                    ->where('id', $id)
                    ->where('department_id', $admin->department_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($appointment->status === 'cancelled') {
                    throw ValidationException::withMessages([
                        'status' => 'Appointment already cancelled.',
                    ]);
                }

                if ($appointment->status === 'confirmed') {
                    if (! $appointment->qr_token) {
                        $appointment->update(['qr_token' => Str::random(64)]);
                    }
                    return $appointment;
                }

                $schedule = DepartmentSchedule::query()
                    ->where('id', $appointment->schedule_id)
                    ->lockForUpdate()
                    ->first();

                if (! $schedule) {
                    throw ValidationException::withMessages([
                        'schedule' => 'Schedule not found for this appointment.',
                    ]);
                }

                $confirmedCount = Appointment::query()
                    ->where('schedule_id', $schedule->id)
                    ->where('status', 'confirmed')
                    ->lockForUpdate()
                    ->count();

                if ($confirmedCount >= (int) $schedule->total_slots) {
                    throw ValidationException::withMessages([
                        'date' => 'Daily capacity is already full. Cannot confirm this appointment.',
                    ]);
                }

                $appointment->update([
                    'status' => 'confirmed',
                    'qr_token' => $appointment->qr_token ?: Str::random(64),
                    'qr_expires_at' => Carbon::parse($appointment->date)->endOfDay(),
                ]);

                return $appointment;
            });

            $appointment->load(['type', 'department', 'user']);

            $crewEmail = $appointment->user?->email;

            Log::info('Appointment confirmed - crew recipient', [
                'appointment_id' => $appointment->id,
                'crew_email' => $crewEmail,
            ]);

            if ($crewEmail) {
                Mail::to($crewEmail)->send(
                    new AppointmentConfirmedMail($appointment, $appointment->user, $appointment->department)
                );
            }

            return response()->json(['success' => true, 'message' => 'Appointment confirmed']);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $admin = Auth::user();

        if ($admin->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $appointment = Appointment::query()
            ->where('id', $id)
            ->where('department_id', $admin->department_id)
            ->firstOrFail();

        if ($appointment->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Appointment already cancelled'], 400);
        }

        DB::transaction(function () use ($appointment, $validated, $admin) {
            $appointment->update([
                'status' => 'cancelled',
                'qr_token' => null,
            ]);

            AppointmentCancellation::create([
                'appointment_id' => $appointment->id,
                'cancelled_by' => $admin->id,
                'cancelled_by_type' => 'department',
                'reason' => $validated['reason'],
                'cancelled_at' => now(),
            ]);
        });

        $appointment->load(['type', 'department', 'user']);

        $cancellation = AppointmentCancellation::query()
            ->where('appointment_id', $appointment->id)
            ->latest('cancelled_at')
            ->first();

        $crewEmail = $appointment->user?->email;

        Log::info('Appointment cancelled by department - crew recipient', [
            'appointment_id' => $appointment->id,
            'crew_email' => $crewEmail,
            'cancellation_id' => $cancellation?->id,
        ]);

        if ($crewEmail && $cancellation) {
            Mail::to($crewEmail)->send(
                new AppointmentCancelledMail($appointment, $cancellation, $appointment->user, $appointment->department)
            );
        }

        return response()->json(['success' => true, 'message' => 'Appointment cancelled']);
    }
}
