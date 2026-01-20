<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QrAppointmentController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $appointment = Appointment::query()
                    ->where('qr_token', $validated['token'])
                    ->lockForUpdate()
                    ->first();

                if (! $appointment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid QR code.',
                    ], 404);
                }

                $appointment->load(['type', 'department', 'user.profile']);

                if ($appointment->status === 'cancelled') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Appointment is cancelled.',
                    ], 422);
                }

                if ($appointment->status === 'pending') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Appointment is not confirmed.',
                    ], 422);
                }

                if (in_array($appointment->status, ['attended', 'no_show'], true)) {
                    return response()->json([
                        'success' => true,
                        'data' => $appointment,
                    ]);
                }

                $expiresAt = $appointment->qr_expires_at
                    ? Carbon::parse($appointment->qr_expires_at)
                    : null;

                if (! $expiresAt) {
                    $date = Carbon::parse($appointment->date)->format('Y-m-d');
                    $cutoffTime = $appointment->session === 'AM' ? '12:00:00' : '23:59:59';
                    $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $cutoffTime);
                }

                if (now()->greaterThan($expiresAt)) {
                    if ($appointment->status === 'confirmed') {
                        $appointment->update([
                            'status' => 'no_show',
                            'qr_token' => null,
                        ]);

                        $appointment->refresh()->load(['type', 'department', 'user.profile']);
                    }

                    return response()->json([
                        'success' => false,
                        'message' => 'QR code expired. Appointment already passed.',
                    ], 410);
                }

                if ($appointment->status !== 'confirmed') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Appointment is not eligible for attendance update.',
                    ], 422);
                }

                $appointment->update([
                    'status' => 'attended',
                    'qr_token' => null,
                ]);

                $appointment->refresh()->load(['type', 'department', 'user.profile']);

                return response()->json([
                    'success' => true,
                    'data' => $appointment,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('QR verify failed', [
                'token' => $validated['token'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to verify QR.',
            ], 500);
        }
    }
}
