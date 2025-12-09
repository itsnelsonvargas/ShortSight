# Redis Caching Implementation Guide

## Overview

ShortSight now includes comprehensive Redis caching to improve performance and scalability. The implementation provides caching for frequent database queries, user sessions, and analytics data.

## Architecture

### Cache Service
- **File**: `app/Services/RedisCacheService.php`
- **Purpose**: Centralized caching logic with error handling for graceful degradation

### Cache Configuration
- **Cache Driver**: Redis (falls back to file if Redis unavailable)
- **Session Driver**: Redis (falls back to file if Redis unavailable)
- **Separate Databases**: Cache (DB 1) and Sessions (DB 0)

## Cached Data Types

### 1. Slug Lookups
- **Cache Key**: `slug:{slug}`
- **TTL**: 1 hour
- **Usage**: Link redirects for faster URL resolution
- **Invalidation**: Automatic on link updates/deletion

### 2. URL Safety Validation
- **Cache Key**: `url_safety:{url}`
- **TTL**: 24 hours
- **Usage**: Google Safe Browsing API results
- **Invalidation**: On URL changes or manual clearing

### 3. Link Metadata
- **Cache Key**: `link_metadata:{slug}`
- **TTL**: 2 hours
- **Usage**: Link information for API responses
- **Invalidation**: Automatic on link updates

### 4. Analytics Data
- **Cache Key**: `analytics:{slug}`
- **TTL**: 30 minutes
- **Usage**: Click counts and analytics summaries
- **Invalidation**: On new visitor data

### 5. Click Counts
- **Cache Key**: `click_count:{slug}`
- **TTL**: 30 minutes
- **Usage**: Real-time click tracking
- **Implementation**: Uses Redis increment for atomic operations

### 6. User Sessions
- **Cache Key**: `user_session:{user_id}`
- **TTL**: 1 hour
- **Usage**: User session data storage
- **Driver**: Redis session driver

## Artisan Commands

### Cache Warming
```bash
php artisan cache:warm [--force]
```
- Pre-loads frequently accessed data into cache
- Processes link metadata, URL safety, and analytics
- Use `--force` to bypass existing cache checks

### Cache Statistics
```bash
php artisan cache:stats [--detailed]
```
- Shows cache driver and Redis connection status
- Displays Redis information (if available)
- Use `--detailed` to show cache key counts by pattern

### Cache Management
```bash
php artisan cache:clear  # Clear all cache
```

## Environment Configuration

```env
# Cache Configuration
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Redis Connection
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

## Error Handling

The implementation includes comprehensive error handling:

- **Graceful Degradation**: Falls back to file caching if Redis unavailable
- **Logging**: Cache errors are logged but don't break functionality
- **Connection Checks**: Services check Redis availability before operations

## Performance Benefits

### Expected Improvements
- **Slug Redirects**: ~90% faster (cache hit vs database query)
- **URL Validation**: ~95% faster for repeat checks
- **Analytics Queries**: ~80% faster with cached results
- **Session Management**: Improved reliability and performance

### Cache Hit Scenarios
- Repeated access to the same short links
- Multiple URL safety checks for the same URLs
- Analytics dashboard views
- User authentication and session management

## Cache Invalidation Strategy

### Automatic Invalidation
- **Model Events**: Link model automatically invalidates cache on updates
- **Middleware**: CacheInvalidationMiddleware handles request-based invalidation
- **TTL Expiration**: Time-based cache expiration for stale data

### Manual Invalidation
```php
$cacheService = app(RedisCacheService::class);

// Invalidate specific caches
$cacheService->invalidateSlugCache('abc123');
$cacheService->invalidateUrlSafetyCache('https://example.com');

// Clear all cache
$cacheService->clearAllCache();
```

## Docker Integration

Redis is included in the Docker Compose setup:

```yaml
redis:
  image: redis:7-alpine
  container_name: shortsight_redis
  ports:
    - "6379:6379"
```

## Monitoring and Maintenance

### Cache Statistics
Monitor cache effectiveness with:
```bash
php artisan cache:stats --detailed
```

### Cache Warming
Regularly warm the cache for optimal performance:
```bash
# Add to cron or scheduler
0 */4 * * * php artisan cache:warm
```

### Health Checks
The cache service provides health check methods:
```php
$stats = $cacheService->getCacheStats();
// Returns: ['redis_connected' => bool, 'cache_driver' => string, 'session_driver' => string]
```

## Best Practices

1. **Monitor Cache Hit Rates**: Use cache statistics to optimize TTL values
2. **Regular Warming**: Schedule cache warming during low-traffic periods
3. **Error Monitoring**: Watch for cache-related errors in logs
4. **Memory Management**: Monitor Redis memory usage in production
5. **Backup Strategy**: Consider Redis persistence for production deployments

## Troubleshooting

### Common Issues

1. **Redis Not Available**
   - Check if Redis container is running: `docker ps`
   - Verify Redis extension is installed
   - Application falls back to file caching automatically

2. **Cache Not Working**
   - Verify CACHE_DRIVER and SESSION_DRIVER settings
   - Check Redis connection in cache stats
   - Review Laravel logs for cache errors

3. **Stale Cache Data**
   - Use appropriate TTL values
   - Monitor cache invalidation events
   - Clear cache manually if needed

## Future Enhancements

- **Cache Clustering**: Redis cluster support for horizontal scaling
- **Cache Analytics**: Detailed cache hit/miss metrics
- **Smart Warming**: AI-based cache warming based on access patterns
- **Distributed Caching**: Multi-region cache replication
