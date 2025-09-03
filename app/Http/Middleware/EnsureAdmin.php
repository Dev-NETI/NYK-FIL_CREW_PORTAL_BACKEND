<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Check if user is admin (is_crew = 0)
        if ($request->user()->is_crew == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Administrator access required.',
                'redirect_to' => '/home'
            ], 403);
        }

        return $next($request);
    }
}
