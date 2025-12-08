<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class LinkCreationRateLimit
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
     * Rate limiting specifically for link creation to prevent spam.
     * 10 links per minute per IP, 50 per hour, 200 per day.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);

        // Check minute-based limit (10 links per minute)
        $minuteKey = $key . ':minute';
        if ($this->limiter->tooManyAttempts($minuteKey, 10)) {
            return $this->buildMinuteResponse($minuteKey);
        }

        // Check hour-based limit (50 links per hour)
        $hourlyKey = $key . ':hourly';
        if ($this->limiter->tooManyAttempts($hourlyKey, 50)) {
            return $this->buildHourlyResponse($hourlyKey);
        }

        // Check daily-based limit (200 links per day)
        $dailyKey = $key . ':daily';
        if ($this->limiter->tooManyAttempts($dailyKey, 200)) {
            return $this->buildDailyResponse($dailyKey);
        }

        $this->limiter->hit($minuteKey, 60);      // 1 minute
        $this->limiter->hit($hourlyKey, 3600);    // 1 hour
        $this->limiter->hit($dailyKey, 86400);    // 24 hours

        $response = $next($request);

        return $this->addHeaders(
            $response,
            10, // minute limit
            $this->calculateRemainingAttempts($minuteKey, 10),
            $this->limiter->availableIn($minuteKey)
        );
    }

    /**
     * Resolve request signature.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        return sha1(
            'link_creation|' . $request->ip()
        );
    }

    /**
     * Create a rate limit exceeded response for minute limit.
     */
    protected function buildMinuteResponse(string $key): Response
    {
        $response = response()->json([
            'message' => 'Too many links created. Please wait before creating more links.',
            'error' => 'link_creation_rate_limit_exceeded',
            'retry_after' => $this->limiter->availableIn($key),
            'limit' => '10 links per minute',
        ], 429);

        return $this->addHeaders(
            $response,
            10,
            $this->calculateRemainingAttempts($key, 10),
            $this->limiter->availableIn($key)
        );
    }

    /**
     * Create a rate limit exceeded response for hourly limit.
     */
    protected function buildHourlyResponse(string $key): Response
    {
        $response = response()->json([
            'message' => 'Hourly link creation limit exceeded.',
            'error' => 'link_creation_hourly_limit_exceeded',
            'retry_after' => $this->limiter->availableIn($key),
            'limit' => '50 links per hour',
        ], 429);

        return $this->addHeaders(
            $response,
            50,
            $this->calculateRemainingAttempts($key, 50),
            $this->limiter->availableIn($key)
        );
    }

    /**
     * Create a rate limit exceeded response for daily limit.
     */
    protected function buildDailyResponse(string $key): Response
    {
        $response = response()->json([
            'message' => 'Daily link creation limit exceeded. Please try again tomorrow.',
            'error' => 'link_creation_daily_limit_exceeded',
            'retry_after' => $this->limiter->availableIn($key),
            'limit' => '200 links per day',
        ], 429);

        return $this->addHeaders(
            $response,
            200,
            $this->calculateRemainingAttempts($key, 200),
            $this->limiter->availableIn($key)
        );
    }

    /**
     * Add the standard rate limiting headers to the response.
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts, int $retryAfter): Response
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
    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        return $maxAttempts - $this->limiter->attempts($key);
    }
}
