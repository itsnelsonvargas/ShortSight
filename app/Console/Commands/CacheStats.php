<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RedisCacheService;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class CacheStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:stats {--detailed : Show detailed cache information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display Redis cache statistics and information';

    protected $cacheService;

    public function __construct(RedisCacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cache Statistics');
        $this->line('================');

        // Basic cache info
        $this->line('Cache Driver: ' . config('cache.default'));
        $this->line('Session Driver: ' . config('session.driver'));
        $this->newLine();

        // Check if Redis is available
        $redisAvailable = $this->isRedisAvailable();

        if ($redisAvailable) {
            $this->line('Redis Status: <info>Available</info>');

            try {
                $stats = $this->cacheService->getCacheStats();
                $detailed = $this->option('detailed');

                if ($stats['redis_connected']) {
                    $this->displayRedisStats($detailed);
                } else {
                    $this->warn('Redis is configured but not responding to ping.');
                }
            } catch (\Exception $e) {
                $this->error('Failed to retrieve Redis stats: ' . $e->getMessage());
            }
        } else {
            $this->line('Redis Status: <comment>Not Available</comment>');
            $this->warn('Redis PHP extension is not installed or Redis server is not running.');
            $this->info('The application will fall back to file-based caching.');
        }

        return 0;
    }

    /**
     * Display Redis-specific statistics
     *
     * @param bool $detailed
     */
    protected function displayRedisStats(bool $detailed = false): void
    {
        $this->line('Redis Information:');
        $this->line('------------------');

        try {
            $info = Redis::info();

            $this->line('Redis Version: ' . ($info['redis_version'] ?? 'Unknown'));
            $this->line('Uptime: ' . ($info['uptime_in_seconds'] ? gmdate('H:i:s', $info['uptime_in_seconds']) : 'Unknown'));
            $this->line('Connected Clients: ' . ($info['connected_clients'] ?? 'Unknown'));
            $this->line('Used Memory: ' . ($info['used_memory_human'] ?? 'Unknown'));
            $this->line('Total Connections: ' . ($info['total_connections_received'] ?? 'Unknown'));

            if ($detailed) {
                $this->newLine();
                $this->line('Cache Keys by Pattern:');
                $this->displayCacheKeys();
            }
        } catch (\Exception $e) {
            $this->warn('Could not retrieve Redis info: ' . $e->getMessage());
        }
    }

    /**
     * Display cache keys grouped by pattern
     */
    protected function displayCacheKeys(): void
    {
        $patterns = [
            'slug:*' => 'Slug lookups',
            'url_safety:*' => 'URL safety checks',
            'link_metadata:*' => 'Link metadata',
            'analytics:*' => 'Analytics data',
            'click_count:*' => 'Click counts',
            'user_session:*' => 'User sessions',
        ];

        foreach ($patterns as $pattern => $description) {
            try {
                $keys = Redis::keys($pattern);
                $count = count($keys);
                $this->line("  {$description}: <comment>{$count}</comment> keys");
            } catch (\Exception $e) {
                $this->line("  {$description}: <error>Error retrieving count</error>");
            }
        }

        // Total keys in cache database
        try {
            $totalKeys = Redis::dbSize();
            $this->line("  <info>Total keys in cache DB: {$totalKeys}</info>");
        } catch (\Exception $e) {
            $this->line("  <error>Could not retrieve total key count</error>");
        }
    }

    /**
     * Check if Redis is available
     *
     * @return bool
     */
    protected function isRedisAvailable(): bool
    {
        // Check if Redis extension is loaded
        if (!extension_loaded('redis')) {
            return false;
        }

        // Check if we can connect to Redis
        try {
            $redis = new \Redis();
            $redis->connect(
                config('database.redis.default.host', '127.0.0.1'),
                config('database.redis.default.port', 6379)
            );

            if (isset(config('database.redis.default.password')[0])) {
                $redis->auth(config('database.redis.default.password'));
            }

            $redis->ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
