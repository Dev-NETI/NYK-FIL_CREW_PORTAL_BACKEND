<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCrew
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

        // Check if user is crew (is_crew = 1)
        if ($request->user()->is_crew != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Crew access required.',
                'redirect_to' => '/admin'
            ], 403);
        }

        return $next($request);
    }
}
