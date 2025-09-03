<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\OtpVerification;
use Carbon\Carbon;

class AuthController extends Controller
{
    private const OTP_EXPIRY_MINUTES = 10;
    private const MAX_OTP_ATTEMPTS = 5;
    private const RATE_LIMIT_KEY = 'auth-attempt:';
    private const MAX_ATTEMPTS_PER_MINUTE = 3;

    public function initiateLogin(Request $request)
    {
        $rateLimitKey = self::RATE_LIMIT_KEY . $request->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, self::MAX_ATTEMPTS_PER_MINUTE)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many login attempts. Please try again later.',
                'retry_after' => RateLimiter::availableIn($rateLimitKey)
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            RateLimiter::hit($rateLimitKey, 60);
            return response()->json([
                'success' => false,
                'message' => 'Invalid email format',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->email;
        $user = User::where('email', $email)->first();

        if (!$user) {
            RateLimiter::hit($rateLimitKey, 60);
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email. Please try again.'
            ], 404);
        }

        // Email verification will happen automatically on first successful OTP login

        $otp = $this->generateSecureOTP();
        $hashedOtp = Hash::make($otp);
        $sessionToken = Str::random(64);

        DB::transaction(function () use ($user, $hashedOtp, $sessionToken) {
            OtpVerification::where('user_id', $user->id)
                ->where('type', 'login')
                ->delete();

            OtpVerification::create([
                'user_id' => $user->id,
                'type' => 'login',
                'otp_hash' => $hashedOtp,
                'session_token' => Hash::make($sessionToken),
                'expires_at' => Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES),
                'attempts' => 0,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        });

        $this->sendOtpToConsole($email, $otp);

        Log::info('OTP login initiated', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your email address',
            'otp' => $otp,
            'session_token' => $sessionToken,
            'expires_in' => self::OTP_EXPIRY_MINUTES * 60
        ]);
    }

    public function verifyOtpAndLogin(Request $request)
    {
        $rateLimitKey = self::RATE_LIMIT_KEY . 'verify:' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, self::MAX_ATTEMPTS_PER_MINUTE)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many verification attempts. Please try again later.',
                'retry_after' => RateLimiter::availableIn($rateLimitKey)
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            RateLimiter::hit($rateLimitKey, 60);
            return response()->json([
                'success' => false,
                'message' => 'Invalid input format',
                'errors' => $validator->errors()
            ], 422);
        }

        $sessionToken = $request->session_token;
        $inputOtp = $request->otp;

        $otpRecord = OtpVerification::where('type', 'login')
            ->where('expires_at', '>', Carbon::now())
            ->get()
            ->filter(function ($record) use ($sessionToken) {
                return Hash::check($sessionToken, $record->session_token);
            })
            ->first();

        if (!$otpRecord) {
            RateLimiter::hit($rateLimitKey, 60);
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired session'
            ], 401);
        }

        if ($otpRecord->attempts >= self::MAX_OTP_ATTEMPTS) {
            $otpRecord->delete();
            RateLimiter::hit($rateLimitKey, 300); // 5-minute penalty
            return response()->json([
                'success' => false,
                'message' => 'Maximum OTP attempts exceeded. Please request a new OTP'
            ], 429);
        }

        $otpRecord->increment('attempts');

        if (!Hash::check($inputOtp, $otpRecord->otp_hash)) {
            RateLimiter::hit($rateLimitKey, 60);
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
                'attempts_remaining' => self::MAX_OTP_ATTEMPTS - $otpRecord->attempts
            ], 401);
        }

        $user = $otpRecord->user;

        $user->tokens()->delete();

        $token = $user->createToken('auth-token', ['*'], Carbon::now()->addHours(24));

        $otpRecord->delete();

        // Update login tracking and verify email if this is the first successful OTP login
        $updateData = [
            'last_login_at' => Carbon::now(),
            'last_login_ip' => $request->ip()
        ];

        // If email is not verified, verify it now (first successful OTP login)
        if (!$user->email_verified_at) {
            $updateData['email_verified_at'] = Carbon::now();
        }

        $user->update($updateData);

        Log::info('User logged in successfully via OTP', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'is_crew' => $user->is_crew,
                'role' => $user->is_crew == 1 ? 'crew' : 'admin'
            ],
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
            'redirect_to' => $user->is_crew == 1 ? '/crew/home' : '/admin'
        ]);
    }

    public function resendOtp(Request $request)
    {
        $rateLimitKey = self::RATE_LIMIT_KEY . 'resend:' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 2)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many resend attempts. Please wait before requesting another OTP.',
                'retry_after' => RateLimiter::availableIn($rateLimitKey)
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid session token',
                'errors' => $validator->errors()
            ], 422);
        }

        $sessionToken = $request->session_token;

        $otpRecord = OtpVerification::where('type', 'login')
            ->where('expires_at', '>', Carbon::now())
            ->get()
            ->filter(function ($record) use ($sessionToken) {
                return Hash::check($sessionToken, $record->session_token);
            })
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired session'
            ], 401);
        }

        $otp = $this->generateSecureOTP();
        $hashedOtp = Hash::make($otp);

        $otpRecord->update([
            'otp_hash' => $hashedOtp,
            'expires_at' => Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'attempts' => 0,
        ]);

        $this->sendOtpToConsole($otpRecord->user->email, $otp);

        RateLimiter::hit($rateLimitKey, 120);

        Log::info('OTP resent', [
            'user_id' => $otpRecord->user->id,
            'email' => $otpRecord->user->email,
            'ip' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'New OTP sent to your email address',
            'expires_in' => self::OTP_EXPIRY_MINUTES * 60
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        Log::info('User logged out', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'email_verified_at' => $request->user()->email_verified_at,
                'last_login_at' => $request->user()->last_login_at
            ]
        ]);
    }

    private function generateSecureOTP(): string
    {
        do {
            $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (strlen($otp) !== 6 || !ctype_digit($otp));

        return $otp;
    }

    private function sendOtpToConsole(string $email, string $otp): void
    {
        // Log OTP information (will appear in Laravel logs and server console)
        Log::info("=== OTP VERIFICATION ===");
        Log::info("Email: {$email}");
        Log::info("OTP Code: {$otp}");
        Log::info("Expires: " . Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES)->format('Y-m-d H:i:s'));
        Log::info("========================");

        // Note: Removed echo statements as they corrupt JSON API responses
        // Check your Laravel logs or server console for the OTP code
    }
}
