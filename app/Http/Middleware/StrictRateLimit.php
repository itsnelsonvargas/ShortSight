<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class StrictRateLimit
{
    /**
     * The rate limiter instance.
     */
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * Very strict rate limiting for authentication endpoints.
     * 5 attempts per minute per IP, 20 per hour.
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = '5', string $decayMinutes = '1'): Response
    {
        $key = $this->resolveRequestSignature($request);

        // Check minute-based limit
        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($key, $maxAttempts, $decayMinutes);
        }

        // Check hour-based limit (20 attempts per hour)
        $hourlyKey = $key . ':hourly';
        if ($this->limiter->tooManyAttempts($hourlyKey, 20)) {
            return $this->buildHourlyResponse($hourlyKey);
        }

        $this->limiter->hit($key, $decayMinutes * 60);
        $this->limiter->hit($hourlyKey, 3600); // 1 hour

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts),
            $this->limiter->availableIn($key)
        );
    }

    /**
     * Resolve request signature.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        return sha1(
            $request->method() .
            '|' . $request->ip() .
            '|' . $request->path()
        );
    }

    /**
     * Create a rate limit exceeded response.
     */
    protected function buildResponse(string $key, string $maxAttempts, string $decayMinutes): Response
    {
        $response = response()->json([
            'message' => 'Too many requests. Please try again later.',
            'error' => 'rate_limit_exceeded',
            'retry_after' => $this->limiter->availableIn($key),
            'limit' => $maxAttempts . ' per ' . $decayMinutes . ' minute(s)',
        ], 429);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts),
            $this->limiter->availableIn($key)
        );
    }

    /**
     * Create a rate limit exceeded response for hourly limit.
     */
    protected function buildHourlyResponse(string $key): Response
    {
        $response = response()->json([
            'message' => 'Too many requests. Hourly limit exceeded.',
            'error' => 'hourly_rate_limit_exceeded',
            'retry_after' => $this->limiter->availableIn($key),
            'limit' => '20 per hour',
        ], 429);

        return $this->addHeaders(
            $response,
            20,
            $this->calculateRemainingAttempts($key, 20),
            $this->limiter->availableIn($key)
        );
    }

    /**
     * Add the standard rate limiting headers to the response.
     */
    protected function addHeaders(Response $response, string $maxAttempts, int $remainingAttempts, int $retryAfter): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
            'X-RateLimit-Reset' => time() + $retryAfter,
            'Retry-After' => $retryAfter,
        ]);

        return $response;
    }

    /**
     * Calculate the number of remaining attempts.
     */
    protected function calculateRemainingAttempts(string $key, string $maxAttempts): int
    {
        return $maxAttempts - $this->limiter->attempts($key);
    }
}
