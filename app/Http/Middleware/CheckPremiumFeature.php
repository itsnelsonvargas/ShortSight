<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPremiumFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated (basic premium check)
        if (!auth()->check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'This feature requires a premium account. Please upgrade to access password protection.',
                    'type' => 'premium_required'
                ], 403);
            }

            return redirect('/login')->withErrors([
                'premium' => 'This feature requires a premium account. Please upgrade to access password protection.'
            ]);
        }

        // TODO: Add actual subscription check here when subscription system is implemented
        // For now, authenticated users are considered premium

        return $next($request);
    }
}
