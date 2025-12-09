<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\RedisCacheService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RedisCacheService::class, function ($app) {
            return new RedisCacheService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure Redis is properly configured
        if (config('cache.default') === 'redis') {
            try {
                \Redis::ping();
            } catch (\Exception $e) {
                \Log::warning('Redis connection failed, falling back to file cache', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
