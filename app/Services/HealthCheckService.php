<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class HealthCheckService
{
    /**
     * Check database connectivity with graceful fallback
     *
     * @return array
     */
    public function checkDatabaseConnection(): array
    {
        try {
            $startTime = microtime(true);

            // Simple query to test connection
            DB::select('SELECT 1');

            $responseTime = round((microtime(true) - $startTime) * 1000, 2); // ms

            return [
                'status' => 'healthy',
                'response_time_ms' => $responseTime,
                'message' => 'Database connection is healthy',
            ];
        } catch (\Exception $e) {
            Log::error('Database health check failed', [
                'error' => $e->getMessage(),
                'connection' => config('database.default'),
            ]);

            return [
                'status' => 'unhealthy',
                'response_time_ms' => null,
                'message' => 'Database connection failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check Redis/cache connectivity
     *
     * @return array
     */
    public function checkCacheConnection(): array
    {
        try {
            $startTime = microtime(true);

            // Test cache connection
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test_value', 10);
            $value = Cache::get($testKey);
            Cache::forget($testKey);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2); // ms

            if ($value === 'test_value') {
                return [
                    'status' => 'healthy',
                    'response_time_ms' => $responseTime,
                    'message' => 'Cache connection is healthy',
                    'driver' => config('cache.default'),
                ];
            } else {
                throw new \Exception('Cache read/write test failed');
            }
        } catch (\Exception $e) {
            Log::error('Cache health check failed', [
                'error' => $e->getMessage(),
                'driver' => config('cache.default'),
            ]);

            return [
                'status' => 'unhealthy',
                'response_time_ms' => null,
                'message' => 'Cache connection failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'driver' => config('cache.default'),
            ];
        }
    }

    /**
     * Check Redis connectivity specifically
     *
     * @return array
     */
    public function checkRedisConnection(): array
    {
        try {
            $startTime = microtime(true);

            $pingResult = Redis::ping();

            $responseTime = round((microtime(true) - $startTime) * 1000, 2); // ms

            if ($pingResult === 'PONG') {
                return [
                    'status' => 'healthy',
                    'response_time_ms' => $responseTime,
                    'message' => 'Redis connection is healthy',
                ];
            } else {
                throw new \Exception('Redis ping returned unexpected result: ' . $pingResult);
            }
        } catch (\Exception $e) {
            Log::error('Redis health check failed', [
                'error' => $e->getMessage(),
                'host' => config('database.redis.default.host', 'unknown'),
                'port' => config('database.redis.default.port', 'unknown'),
            ]);

            return [
                'status' => 'unhealthy',
                'response_time_ms' => null,
                'message' => 'Redis connection failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Comprehensive health check for all services
     *
     * @return array
     */
    public function performFullHealthCheck(): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'services' => [
                'database' => $this->checkDatabaseConnection(),
                'cache' => $this->checkCacheConnection(),
                'redis' => $this->checkRedisConnection(),
            ],
            'overall_status' => $this->calculateOverallStatus([
                $this->checkDatabaseConnection(),
                $this->checkCacheConnection(),
                $this->checkRedisConnection(),
            ]),
        ];
    }

    /**
     * Calculate overall system status
     *
     * @param array $serviceStatuses
     * @return string
     */
    private function calculateOverallStatus(array $serviceStatuses): string
    {
        $criticalServices = ['database']; // Services that are critical for operation
        $healthyCount = 0;
        $totalCount = count($serviceStatuses);

        foreach ($serviceStatuses as $status) {
            if ($status['status'] === 'healthy') {
                $healthyCount++;
            }
        }

        // If all services are healthy
        if ($healthyCount === $totalCount) {
            return 'healthy';
        }

        // Check if critical services are down
        $databaseHealthy = collect($serviceStatuses)->firstWhere('service', 'database')['status'] === 'healthy';

        if (!$databaseHealthy) {
            return 'critical';
        }

        // Some services are down but not critical ones
        return 'degraded';
    }

    /**
     * Get system status for API endpoints
     *
     * @return array
     */
    public function getSystemStatus(): array
    {
        $health = $this->performFullHealthCheck();

        return [
            'status' => $health['overall_status'],
            'timestamp' => $health['timestamp'],
            'version' => config('app.version', '1.0.0'),
            'services' => [
                'database' => $health['services']['database']['status'],
                'cache' => $health['services']['cache']['status'],
                'redis' => $health['services']['redis']['status'],
            ],
            'response_time' => [
                'database' => $health['services']['database']['response_time_ms'],
                'cache' => $health['services']['cache']['response_time_ms'],
                'redis' => $health['services']['redis']['redis']['response_time_ms'] ?? null,
            ],
        ];
    }

    /**
     * Check if system is in maintenance mode
     *
     * @return bool
     */
    public function isInMaintenance(): bool
    {
        return app()->isDownForMaintenance();
    }

    /**
     * Gracefully handle database operations with fallback
     *
     * @param callable $operation
     * @param mixed $fallbackValue
     * @return mixed
     */
    public function executeWithFallback(callable $operation, $fallbackValue = null)
    {
        try {
            return $operation();
        } catch (\Exception $e) {
            Log::error('Database operation failed, using fallback', [
                'error' => $e->getMessage(),
                'fallback' => $fallbackValue,
            ]);

            return $fallbackValue;
        }
    }

    /**
     * Execute operation with degraded performance mode
     *
     * @param callable $primaryOperation
     * @param callable $fallbackOperation
     * @return mixed
     */
    public function executeWithDegradedMode(callable $primaryOperation, callable $fallbackOperation)
    {
        try {
            return $primaryOperation();
        } catch (\Exception $e) {
            Log::warning('Primary operation failed, switching to degraded mode', [
                'error' => $e->getMessage(),
            ]);

            try {
                return $fallbackOperation();
            } catch (\Exception $fallbackError) {
                Log::error('Fallback operation also failed', [
                    'primary_error' => $e->getMessage(),
                    'fallback_error' => $fallbackError->getMessage(),
                ]);

                throw $fallbackError;
            }
        }
    }
}
