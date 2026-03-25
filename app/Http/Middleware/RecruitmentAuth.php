<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecruitmentAuth
{
    /**
     * Validate the Recruitment App shared-secret Bearer token.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.recruitment.shared_secret');

        if (! $secret) {
            return response()->json([
                'success' => false,
                'message' => 'Recruitment API integration is not configured on this server.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $authHeader = $request->header('Authorization', '');
        $token = str_starts_with($authHeader, 'Bearer ')
            ? substr($authHeader, 7)
            : null;

        if (! $token || ! hash_equals($secret, $token)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing Recruitment API secret key.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
