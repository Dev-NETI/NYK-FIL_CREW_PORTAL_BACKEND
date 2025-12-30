<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentCancellation;
use App\Models\DepartmentSchedule;
use Carbon\Carbon;
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
            'data' => $query->orderBy('date', 'desc')->orderBy('time')->get(),
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
                'appointments as booked_slots' => fn ($q) => $q->whereIn('status', ['pending', 'confirmed']),
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
        $user = Auth::user();

        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $appointment = Appointment::query()
            ->where('id', $id)
            ->where('department_id', $user->department_id)
            ->firstOrFail();

        if ($appointment->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Appointment already cancelled'], 400);
        }

        if ($appointment->status === 'confirmed') {
            return response()->json(['success' => true, 'message' => 'Appointment already confirmed']);
        }

        $appointment->update(['status' => 'confirmed']);

        return response()->json(['success' => true, 'message' => 'Appointment confirmed']);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $user = Auth::user();

        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $appointment = Appointment::query()
            ->where('id', $id)
            ->where('department_id', $user->department_id)
            ->firstOrFail();

        if ($appointment->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Appointment already cancelled'], 400);
        }

        DB::transaction(function () use ($appointment, $validated, $user) {
            $appointment->update(['status' => 'cancelled']);

            AppointmentCancellation::create([
                'appointment_id' => $appointment->id,
                'cancelled_by' => $user->id,
                'cancelled_by_type' => 'department',
                'reason' => $validated['reason'],
                'cancelled_at' => now(),
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Appointment cancelled']);
    }
}
