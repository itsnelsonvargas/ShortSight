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
     * Configurable limits via environment variables.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);

        $minuteLimit = (int) env('LINK_CREATION_LIMIT_MINUTE', 10);
        $hourlyLimit = (int) env('LINK_CREATION_LIMIT_HOUR', 50);
        $dailyLimit = (int) env('LINK_CREATION_LIMIT_DAY', 200);

        // Check minute-based limit
        $minuteKey = $key . ':minute';
        if ($this->limiter->tooManyAttempts($minuteKey, $minuteLimit)) {
            return $this->buildMinuteResponse($minuteKey, $minuteLimit);
        }

        // Check hour-based limit
        $hourlyKey = $key . ':hourly';
        if ($this->limiter->tooManyAttempts($hourlyKey, $hourlyLimit)) {
            return $this->buildHourlyResponse($hourlyKey, $hourlyLimit);
        }

        // Check daily-based limit
        $dailyKey = $key . ':daily';
        if ($this->limiter->tooManyAttempts($dailyKey, $dailyLimit)) {
            return $this->buildDailyResponse($dailyKey, $dailyLimit);
        }

        $this->limiter->hit($minuteKey, 60);      // 1 minute
        $this->limiter->hit($hourlyKey, 3600);    // 1 hour
        $this->limiter->hit($dailyKey, 86400);    // 24 hours

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $minuteLimit,
            $this->calculateRemainingAttempts($minuteKey, $minuteLimit),
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
    protected function buildMinuteResponse(string $key, int $limit): Response
    {
        $response = response()->json([
            'message' => 'Too many links created. Please wait before creating more links.',
            'error' => 'link_creation_rate_limit_exceeded',
            'retry_after' => $this->limiter->availableIn($key),
            'limit' => $limit . ' links per minute',
        ], 429);

        return $this->addHeaders(
            $response,
            $limit,
            $this->calculateRemainingAttempts($key, $limit),
            $this->limiter->availableIn($key)
        );
    }

    /**
     * Create a rate limit exceeded response for hourly limit.
     */
    protected function buildHourlyResponse(string $key, int $limit): Response
    {
        $response = response()->json([
            'message' => 'Hourly link creation limit exceeded.',
            'error' => 'link_creation_hourly_limit_exceeded',
            'retry_after' => $this->limiter->availableIn($key),
            'limit' => $limit . ' links per hour',
        ], 429);

        return $this->addHeaders(
            $response,
            $limit,
            $this->calculateRemainingAttempts($key, $limit),
            $this->limiter->availableIn($key)
        );
    }

    /**
     * Create a rate limit exceeded response for daily limit.
     */
    protected function buildDailyResponse(string $key, int $limit): Response
    {
        $response = response()->json([
            'message' => 'Daily link creation limit exceeded. Please try again tomorrow.',
            'error' => 'link_creation_daily_limit_exceeded',
            'retry_after' => $this->limiter->availableIn($key),
            'limit' => $limit . ' links per day',
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
