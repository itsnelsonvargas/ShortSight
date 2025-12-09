<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Models\Link;
use App\Models\Visitor;

class RedisCacheService
{
    /**
     * Cache TTL for different types of data (in seconds)
     */
    const CACHE_TTL = [
        'slug_lookup' => 3600, // 1 hour - slug lookups are frequent
        'url_validation' => 86400, // 24 hours - URL safety doesn't change often
        'analytics' => 1800, // 30 minutes - analytics can be slightly stale
        'link_metadata' => 7200, // 2 hours - link metadata changes rarely
        'user_session' => 3600, // 1 hour - session data
    ];

    /**
     * Cache a slug lookup result
     *
     * @param string $slug
     * @param Link|null $link
     * @return void
     */
    public function cacheSlugLookup(string $slug, ?Link $link): void
    {
        try {
            $cacheKey = "slug:{$slug}";
            Cache::put($cacheKey, $link, self::CACHE_TTL['slug_lookup']);
        } catch (\Exception $e) {
            // Log cache error but don't fail the operation
            \Log::warning('Failed to cache slug lookup', [
                'slug' => $slug,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get cached slug lookup result
     *
     * @param string $slug
     * @return Link|null
     */
    public function getCachedSlugLookup(string $slug): ?Link
    {
        try {
            $cacheKey = "slug:{$slug}";
            return Cache::get($cacheKey);
        } catch (\Exception $e) {
            // Log cache error and return null to fall back to database
            \Log::warning('Failed to get cached slug lookup', [
                'slug' => $slug,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Cache URL safety validation result
     *
     * @param string $url
     * @param bool $isSafe
     * @return void
     */
    public function cacheUrlSafety(string $url, bool $isSafe): void
    {
        try {
            $cacheKey = "url_safety:{$url}";
            Cache::put($cacheKey, $isSafe, self::CACHE_TTL['url_validation']);
        } catch (\Exception $e) {
            \Log::warning('Failed to cache URL safety', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get cached URL safety result
     *
     * @param string $url
     * @return bool|null
     */
    public function getCachedUrlSafety(string $url): ?bool
    {
        try {
            $cacheKey = "url_safety:{$url}";
            return Cache::get($cacheKey);
        } catch (\Exception $e) {
            \Log::warning('Failed to get cached URL safety', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Cache link metadata
     *
     * @param Link $link
     * @return void
     */
    public function cacheLinkMetadata(Link $link): void
    {
        $cacheKey = "link_metadata:{$link->slug}";
        $metadata = [
            'id' => $link->id,
            'url' => $link->url,
            'slug' => $link->slug,
            'created_at' => $link->created_at,
            'updated_at' => $link->updated_at,
        ];
        Cache::put($cacheKey, $metadata, self::CACHE_TTL['link_metadata']);
    }

    /**
     * Get cached link metadata
     *
     * @param string $slug
     * @return array|null
     */
    public function getCachedLinkMetadata(string $slug): ?array
    {
        $cacheKey = "link_metadata:{$slug}";
        return Cache::get($cacheKey);
    }

    /**
     * Cache analytics data
     *
     * @param string $slug
     * @param array $analytics
     * @return void
     */
    public function cacheAnalyticsData(string $slug, array $analytics): void
    {
        $cacheKey = "analytics:{$slug}";
        Cache::put($cacheKey, $analytics, self::CACHE_TTL['analytics']);
    }

    /**
     * Get cached analytics data
     *
     * @param string $slug
     * @return array|null
     */
    public function getCachedAnalyticsData(string $slug): ?array
    {
        $cacheKey = "analytics:{$slug}";
        return Cache::get($cacheKey);
    }

    /**
     * Cache user session data
     *
     * @param int $userId
     * @param array $sessionData
     * @return void
     */
    public function cacheUserSession(int $userId, array $sessionData): void
    {
        $cacheKey = "user_session:{$userId}";
        Cache::put($cacheKey, $sessionData, self::CACHE_TTL['user_session']);
    }

    /**
     * Get cached user session data
     *
     * @param int $userId
     * @return array|null
     */
    public function getCachedUserSession(int $userId): ?array
    {
        $cacheKey = "user_session:{$userId}";
        return Cache::get($cacheKey);
    }

    /**
     * Invalidate slug cache when link is updated/deleted
     *
     * @param string $slug
     * @return void
     */
    public function invalidateSlugCache(string $slug): void
    {
        Cache::forget("slug:{$slug}");
        Cache::forget("link_metadata:{$slug}");
        Cache::forget("analytics:{$slug}");
    }

    /**
     * Invalidate URL safety cache
     *
     * @param string $url
     * @return void
     */
    public function invalidateUrlSafetyCache(string $url): void
    {
        Cache::forget("url_safety:{$url}");
    }

    /**
     * Invalidate user session cache
     *
     * @param int $userId
     * @return void
     */
    public function invalidateUserSession(int $userId): void
    {
        Cache::forget("user_session:{$userId}");
    }

    /**
     * Cache link click counts for analytics
     *
     * @param string $slug
     * @param int $count
     * @return void
     */
    public function cacheLinkClickCount(string $slug, int $count): void
    {
        $cacheKey = "click_count:{$slug}";
        Cache::put($cacheKey, $count, self::CACHE_TTL['analytics']);
    }

    /**
     * Get cached link click count
     *
     * @param string $slug
     * @return int|null
     */
    public function getCachedLinkClickCount(string $slug): ?int
    {
        $cacheKey = "click_count:{$slug}";
        return Cache::get($cacheKey);
    }

    /**
     * Increment cached click count
     *
     * @param string $slug
     * @return int
     */
    public function incrementClickCount(string $slug): int
    {
        $cacheKey = "click_count:{$slug}";
        return Cache::increment($cacheKey, 1);
    }

    /**
     * Get cache statistics
     *
     * @return array
     */
    public function getCacheStats(): array
    {
        $redisConnected = false;
        try {
            $redisConnected = Redis::ping() === 'PONG';
        } catch (\Exception $e) {
            $redisConnected = false;
        }

        return [
            'redis_connected' => $redisConnected,
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
        ];
    }

    /**
     * Clear all cache
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        Cache::flush();
    }

    /**
     * Cache comprehensive URL validation results
     *
     * @param string $url
     * @param array $validationResult
     * @return void
     */
    public function cacheUrlValidation(string $url, array $validationResult): void
    {
        try {
            $cacheKey = "url_validation:{$url}";
            Cache::put($cacheKey, $validationResult, self::CACHE_TTL['url_validation']);
        } catch (\Exception $e) {
            \Log::warning('Failed to cache URL validation', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get cached URL validation results
     *
     * @param string $url
     * @return array|null
     */
    public function getCachedUrlValidation(string $url): ?array
    {
        $cacheKey = "url_validation:{$url}";
        return Cache::get($cacheKey);
    }

    /**
     * Invalidate URL validation cache
     *
     * @param string $url
     * @return void
     */
    public function invalidateUrlValidationCache(string $url): void
    {
        Cache::forget("url_validation:{$url}");
    }

    /**
     * Cache domain blacklist
     *
     * @param array $blacklist
     * @return void
     */
    public function cacheDomainBlacklist(array $blacklist): void
    {
        $cacheKey = "domain_blacklist";
        Cache::put($cacheKey, $blacklist, self::CACHE_TTL['link_metadata']); // Blacklist changes infrequently
    }

    /**
     * Get cached domain blacklist
     *
     * @return array|null
     */
    public function getCachedDomainBlacklist(): ?array
    {
        $cacheKey = "domain_blacklist";
        return Cache::get($cacheKey);
    }

    /**
     * Warm up cache with frequently accessed data
     *
     * @return void
     */
    public function warmCache(): void
    {
        // Cache most recently accessed links
        $recentLinks = Link::latest()->take(100)->get();
        foreach ($recentLinks as $link) {
            $this->cacheLinkMetadata($link);

            // Cache click counts for analytics
            $clickCount = Visitor::where('slug', $link->slug)->count();
            $this->cacheLinkClickCount($link->slug, $clickCount);
        }
    }
}
