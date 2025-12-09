<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\RedisCacheService;

class CacheInvalidationMiddleware
{
    protected $cacheService;

    public function __construct(RedisCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Invalidate cache for specific routes that modify data
        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('delete')) {
            $this->invalidateRelevantCache($request);
        }

        return $response;
    }

    /**
     * Invalidate cache based on the request
     *
     * @param Request $request
     * @return void
     */
    protected function invalidateRelevantCache(Request $request): void
    {
        $routeName = $request->route() ? $request->route()->getName() : null;

        switch ($routeName) {
            case 'api.deleteToken':
                // Invalidate user session cache when token is deleted
                if ($request->user()) {
                    $this->cacheService->invalidateUserSession($request->user()->id);
                }
                break;

            // Add more cases as needed for other cache invalidation scenarios
        }

        // For link-related routes, invalidate URL safety cache if URL was provided
        if ($request->has('url') || $request->has('link')) {
            $url = $request->input('url') ?: $request->input('link');
            if ($url) {
                $this->cacheService->invalidateUrlSafetyCache($url);
            }
        }
    }
}
