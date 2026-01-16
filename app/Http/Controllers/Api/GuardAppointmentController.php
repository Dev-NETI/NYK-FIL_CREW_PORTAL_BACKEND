<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuardAppointmentController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $appointment = Appointment::query()
            ->where('qr_token', $validated['token'])
            ->with(['type', 'department', 'user.profile'])
            ->first();

        if (! $appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code.',
            ], 404);
        }

        if ($appointment->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Appointment is not confirmed.',
            ], 422);
        }

        $scheduledAt = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            Carbon::parse($appointment->date)->format('Y-m-d') . ' ' . $appointment->time
        );

        if (now()->greaterThan($scheduledAt)) {
            return response()->json([
                'success' => false,
                'message' => 'QR is no longer valid. Appointment is already past.',
            ], 410); // Gone
        }

        return response()->json([
            'success' => true,
            'data' => $appointment,
        ]);
    }

}
