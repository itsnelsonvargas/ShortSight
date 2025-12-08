<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Models\Link;
use App\Models\Visitor;

class DatabaseOptimizationService
{
    /**
     * Cache TTL for analytics queries
     */
    const ANALYTICS_CACHE_TTL = 1800; // 30 minutes

    /**
     * Cache TTL for link lookups
     */
    const LINK_CACHE_TTL = 3600; // 1 hour

    /**
     * Get link by slug with optimized caching
     *
     * @param string $slug
     * @return Link|null
     */
    public function getLinkBySlug(string $slug): ?Link
    {
        $cacheKey = "link:{$slug}";

        return Cache::remember($cacheKey, self::LINK_CACHE_TTL, function () use ($slug) {
            return Link::where('slug', $slug)
                ->where('is_disabled', false)
                ->first();
        });
    }

    /**
     * Get link analytics with optimized queries
     *
     * @param string $slug
     * @param array $options
     * @return array
     */
    public function getLinkAnalytics(string $slug, array $options = []): array
    {
        $cacheKey = "analytics:{$slug}:" . md5(serialize($options));

        return Cache::remember($cacheKey, self::ANALYTICS_CACHE_TTL, function () use ($slug, $options) {
            return $this->calculateLinkAnalytics($slug, $options);
        });
    }

    /**
     * Calculate link analytics with optimized queries
     *
     * @param string $slug
     * @param array $options
     * @return array
     */
    private function calculateLinkAnalytics(string $slug, array $options = []): array
    {
        $dateRange = $options['date_range'] ?? 30; // days

        // Use summary table if available and recent
        if (config('database.optimization.analytics.use_summary_tables')) {
            $summary = DB::table('link_statistics_summary')
                ->where('slug', $slug)
                ->where('updated_at', '>', now()->subMinutes(30))
                ->first();

            if ($summary) {
                return $this->formatSummaryAnalytics($summary);
            }
        }

        // Fallback to optimized direct queries
        $analytics = [];

        // Basic stats in single query
        $basicStats = DB::selectOne("
            SELECT
                COUNT(*) as total_clicks,
                COUNT(DISTINCT ip_address) as unique_visitors,
                COUNT(DISTINCT country) as countries_reached,
                MIN(created_at) as first_click,
                MAX(created_at) as last_click
            FROM visitors
            WHERE slug = ?
            AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ", [$slug, $dateRange]);

        $analytics['basic'] = [
            'total_clicks' => (int) $basicStats->total_clicks,
            'unique_visitors' => (int) $basicStats->unique_visitors,
            'countries_reached' => (int) $basicStats->countries_reached,
            'first_click' => $basicStats->first_click,
            'last_click' => $basicStats->last_click,
        ];

        // Geographic data with LIMIT for performance
        $analytics['geographic'] = DB::select("
            SELECT
                country,
                city,
                COUNT(*) as clicks
            FROM visitors
            WHERE slug = ?
            AND country IS NOT NULL
            AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY country, city
            ORDER BY clicks DESC
            LIMIT 50
        ", [$slug, $dateRange]);

        // Device/browser breakdown
        $analytics['devices'] = DB::select("
            SELECT
                device,
                COUNT(*) as clicks
            FROM visitors
            WHERE slug = ?
            AND device IS NOT NULL
            AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY device
            ORDER BY clicks DESC
        ", [$slug, $dateRange]);

        $analytics['browsers'] = DB::select("
            SELECT
                browser,
                COUNT(*) as clicks
            FROM visitors
            WHERE slug = ?
            AND browser IS NOT NULL
            AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY browser
            ORDER BY clicks DESC
        ", [$slug, $dateRange]);

        // Referrers
        $analytics['referrers'] = DB::select("
            SELECT
                referer,
                COUNT(*) as clicks
            FROM visitors
            WHERE slug = ?
            AND referer IS NOT NULL
            AND referer != ''
            AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY referer
            ORDER BY clicks DESC
            LIMIT 20
        ", [$slug, $dateRange]);

        return $analytics;
    }

    /**
     * Format summary table analytics
     *
     * @param object $summary
     * @return array
     */
    private function formatSummaryAnalytics(object $summary): array
    {
        return [
            'basic' => [
                'total_clicks' => (int) $summary->total_clicks,
                'unique_visitors' => (int) $summary->unique_visitors,
                'countries_reached' => (int) $summary->countries_reached,
                'first_click' => $summary->first_click_date,
                'last_click' => $summary->last_click_date,
            ],
            'geographic' => json_decode($summary->top_countries ?? '[]', true),
            'devices' => json_decode($summary->device_breakdown ?? '{}', true),
            'browsers' => json_decode($summary->browser_breakdown ?? '{}', true),
            'referrers' => [], // Not stored in summary
            'from_summary' => true,
        ];
    }

    /**
     * Bulk insert visitors for high-performance tracking
     *
     * @param array $visitorData
     * @return bool
     */
    public function bulkInsertVisitors(array $visitorData): bool
    {
        if (empty($visitorData)) {
            return true;
        }

        try {
            // Use chunking for large datasets
            $chunks = array_chunk($visitorData, config('database.optimization.bulk_insert.batch_size', 1000));

            foreach ($chunks as $chunk) {
                DB::table('visitors')->insert($chunk);
            }

            // Invalidate relevant caches
            $this->invalidateAnalyticsCache(array_unique(array_column($visitorData, 'slug')));

            return true;
        } catch (\Exception $e) {
            \Log::error('Bulk visitor insert failed', [
                'error' => $e->getMessage(),
                'data_count' => count($visitorData)
            ]);
            return false;
        }
    }

    /**
     * Get top performing links with optimized query
     *
     * @param int $limit
     * @param int $days
     * @return array
     */
    public function getTopPerformingLinks(int $limit = 10, int $days = 7): array
    {
        $cacheKey = "top_links:{$limit}:{$days}";

        return Cache::remember($cacheKey, self::ANALYTICS_CACHE_TTL, function () use ($limit, $days) {
            // Use optimized view if available
            if (Schema::hasTable('link_performance')) {
                return DB::select("
                    SELECT * FROM link_performance
                    WHERE month_clicks > 0
                    ORDER BY month_clicks DESC
                    LIMIT ?
                ", [$limit]);
            }

            // Fallback to direct query
            return DB::select("
                SELECT
                    l.id,
                    l.slug,
                    l.title,
                    l.url,
                    l.user,
                    COUNT(v.id) as total_clicks,
                    COUNT(DISTINCT v.ip_address) as unique_visitors
                FROM links l
                LEFT JOIN visitors v ON l.slug = v.slug
                    AND v.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                WHERE l.deleted_at IS NULL
                GROUP BY l.id, l.slug, l.title, l.url, l.user
                ORDER BY total_clicks DESC
                LIMIT ?
            ", [$days, $limit]);
        });
    }

    /**
     * Invalidate analytics cache for specific slugs
     *
     * @param array $slugs
     * @return void
     */
    public function invalidateAnalyticsCache(array $slugs): void
    {
        $keys = [];

        foreach ($slugs as $slug) {
            $keys[] = "analytics:{$slug}:*";
            $keys[] = "link:{$slug}";
        }

        // Also invalidate top links cache
        $keys[] = "top_links:*";

        // Use Redis SCAN or KEYS to delete wildcard patterns
        if (Redis::connection()->getName() === 'cache') {
            foreach ($keys as $pattern) {
                $cursor = 0;
                do {
                    list($cursor, $matchingKeys) = Redis::scan($cursor, ['match' => $pattern]);
                    if (!empty($matchingKeys)) {
                        Redis::del($matchingKeys);
                    }
                } while ($cursor != 0);
            }
        }
    }

    /**
     * Warm up cache for popular links
     *
     * @return void
     */
    public function warmCache(): void
    {
        // Get top 100 most clicked links from last 30 days
        $popularLinks = DB::select("
            SELECT slug, COUNT(*) as clicks
            FROM visitors
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY slug
            ORDER BY clicks DESC
            LIMIT 100
        ");

        foreach ($popularLinks as $link) {
            // Pre-load link data
            $this->getLinkBySlug($link->slug);

            // Pre-load basic analytics
            $this->getLinkAnalytics($link->slug);
        }
    }

    /**
     * Get database performance metrics
     *
     * @return array
     */
    public function getPerformanceMetrics(): array
    {
        $metrics = [];

        // Table sizes
        $tableSizes = DB::select("
            SELECT
                table_name,
                ROUND((data_length + index_length) / 1024 / 1024, 2) as size_mb,
                table_rows
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            AND table_name IN ('links', 'visitors', 'users')
            ORDER BY data_length DESC
        ");

        $metrics['table_sizes'] = $tableSizes;

        // Index usage (if available)
        try {
            $indexStats = DB::select("
                SELECT
                    object_schema,
                    object_name,
                    index_name,
                    count_read,
                    count_fetch,
                    count_insert,
                    count_update,
                    count_delete
                FROM performance_schema.table_io_waits_summary_by_index_usage
                WHERE object_schema = DATABASE()
                AND object_name IN ('links', 'visitors')
                AND index_name IS NOT NULL
                ORDER BY count_read DESC
                LIMIT 20
            ");
            $metrics['index_usage'] = $indexStats;
        } catch (\Exception $e) {
            $metrics['index_usage'] = [];
        }

        // Query performance insights
        $slowQueries = DB::select("
            SELECT
                sql_text,
                exec_count,
                avg_timer_wait / 1000000000 as avg_time_sec,
                max_timer_wait / 1000000000 as max_time_sec
            FROM performance_schema.events_statements_summary_by_digest
            WHERE schema_name = DATABASE()
            AND avg_timer_wait > 1000000000 -- More than 1 second average
            ORDER BY avg_timer_wait DESC
            LIMIT 10
        ");

        $metrics['slow_queries'] = $slowQueries;

        return $metrics;
    }
}
