<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimit
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
     * Moderate rate limiting for general API usage.
     * Configurable limits via environment variables.
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = null, string $decayMinutes = '1'): Response
    {
        $key = $this->resolveRequestSignature($request);

        // Use environment variables if not explicitly passed
        $minuteLimit = $maxAttempts ?: (string) env('API_LIMIT_MINUTE', 100);
        $hourlyLimit = (int) env('API_LIMIT_HOUR', 1000);

        // Check minute-based limit
        if ($this->limiter->tooManyAttempts($key, $minuteLimit)) {
            return $this->buildResponse($key, $minuteLimit, $decayMinutes);
        }

        // Check hour-based limit
        $hourlyKey = $key . ':hourly';
        if ($this->limiter->tooManyAttempts($hourlyKey, $hourlyLimit)) {
            return $this->buildHourlyResponse($hourlyKey, $hourlyLimit);
        }

        $this->limiter->hit($key, $decayMinutes * 60);
        $this->limiter->hit($hourlyKey, 3600); // 1 hour

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $minuteLimit,
            $this->calculateRemainingAttempts($key, $minuteLimit),
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
            'message' => 'API rate limit exceeded. Please try again later.',
            'error' => 'api_rate_limit_exceeded',
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
    protected function buildHourlyResponse(string $key, int $limit): Response
    {
        $response = response()->json([
            'message' => 'API hourly rate limit exceeded.',
            'error' => 'api_hourly_rate_limit_exceeded',
            'retry_after' => $this->limiter->availableIn($key),
            'limit' => $limit . ' per hour',
        ], 429);

        return $this->addHeaders(
            $response,
            $limit,
            $this->calculateRemainingAttempts($key, $limit),
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
