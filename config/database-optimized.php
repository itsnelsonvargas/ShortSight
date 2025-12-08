<?php

use Illuminate\Support\Str;

/**
 * Optimized Database Configuration for High-Scale URL Shortener
 *
 * This configuration is designed to handle millions to hundreds of millions of clicks
 * with optimal performance, memory usage, and reliability.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            // Optimized settings for high-scale operations
            'options' => [
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false, // Stream results for large datasets
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                \PDO::ATTR_EMULATE_PREPARES => false, // Use real prepared statements
                \PDO::ATTR_STRINGIFY_FETCHES => false,
                \PDO::MYSQL_ATTR_FOUND_ROWS => false, // Don't count all rows for performance
            ],
            // Connection pooling settings
            'pool_size' => env('DB_POOL_SIZE', 10),
            'pool_timeout' => env('DB_POOL_TIMEOUT', 30),
            'max_idle_time' => env('DB_MAX_IDLE_TIME', 60),
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
            // Optimized settings for PostgreSQL
            'options' => [
                // PDO optimizations can be added here if needed
            ],
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
            // Optimized for high-throughput caching
            'read_timeout' => 60,
            'timeout' => 10,
            'persistent' => true,
            'persistent_id' => 'shortsight_cache',
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
            // Optimized for cache operations
            'read_timeout' => 30,
            'timeout' => 5,
            'persistent' => true,
            'persistent_id' => 'shortsight_cache_storage',
        ],

        'sessions' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_SESSIONS_DB', 2),
            // Optimized for session storage
            'read_timeout' => 30,
            'timeout' => 5,
            'persistent' => true,
            'persistent_id' => 'shortsight_sessions',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | High-Performance Query Optimization Settings
    |--------------------------------------------------------------------------
    */

    'optimization' => [
        // Query result caching settings
        'query_cache_ttl' => env('DB_QUERY_CACHE_TTL', 300), // 5 minutes
        'result_cache_ttl' => env('DB_RESULT_CACHE_TTL', 600), // 10 minutes

        // Connection pooling
        'pooling' => [
            'enabled' => env('DB_POOLING_ENABLED', true),
            'min_connections' => env('DB_POOL_MIN_CONNECTIONS', 5),
            'max_connections' => env('DB_POOL_MAX_CONNECTIONS', 50),
            'idle_timeout' => env('DB_POOL_IDLE_TIMEOUT', 300), // 5 minutes
        ],

        // Read/write splitting for analytics queries
        'read_write_splitting' => [
            'enabled' => env('DB_READ_WRITE_SPLITTING', false),
            'read_connections' => env('DB_READ_CONNECTIONS', ['mysql_read_1', 'mysql_read_2']),
            'analytics_readonly' => env('DB_ANALYTICS_READONLY', true),
        ],

        // Bulk insert settings for visitor tracking
        'bulk_insert' => [
            'enabled' => env('DB_BULK_INSERT_ENABLED', true),
            'batch_size' => env('DB_BULK_INSERT_BATCH_SIZE', 1000),
            'flush_interval' => env('DB_BULK_INSERT_FLUSH_INTERVAL', 5), // seconds
        ],

        // Analytics query optimization
        'analytics' => [
            'use_summary_tables' => env('DB_USE_SUMMARY_TABLES', true),
            'cache_analytics' => env('DB_CACHE_ANALYTICS', true),
            'analytics_cache_ttl' => env('DB_ANALYTICS_CACHE_TTL', 1800), // 30 minutes
            'precompute_top_links' => env('DB_PRECOMPUTE_TOP_LINKS', true),
        ],
    ],

];
