<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Optimize users table indexes
        Schema::table('users', function (Blueprint $table) {
            // Add composite index for email lookups with verification status
            $table->index(['email', 'email_verified_at'], 'users_email_verified_idx');

            // Add index for social login lookups
            $table->index('google_id', 'users_google_id_idx');
            $table->index('facebook_id', 'users_facebook_id_idx');

            // Add index for created_at for user analytics
            $table->index('created_at', 'users_created_at_idx');
        });

        // Optimize links table indexes
        Schema::table('links', function (Blueprint $table) {
            // Critical: slug index for redirects (most frequent query)
            $table->index('slug', 'links_slug_idx');

            // User-based queries for dashboard
            $table->index('user', 'links_user_idx');

            // Composite index for user + disabled status
            $table->index(['user', 'is_disabled'], 'links_user_disabled_idx');

            // Composite index for user + created_at (sorting dashboard)
            $table->index(['user', 'created_at'], 'links_user_created_at_idx');

            // Soft delete index for cleanup operations
            $table->index('deleted_at', 'links_deleted_at_idx');

            // Composite for active links lookup
            $table->index(['is_disabled', 'created_at'], 'links_active_created_idx');
        });

        // Optimize visitors table for massive scale
        Schema::table('visitors', function (Blueprint $table) {
            // Critical: slug + timestamp for analytics queries
            $table->index(['slug', 'created_at'], 'visitors_slug_created_at_idx');

            // Geographic analytics
            $table->index(['slug', 'country'], 'visitors_slug_country_idx');
            $table->index(['slug', 'city'], 'visitors_slug_city_idx');

            // Device/browser analytics
            $table->index(['slug', 'device'], 'visitors_slug_device_idx');
            $table->index(['slug', 'browser'], 'visitors_slug_browser_idx');
            $table->index(['slug', 'platform'], 'visitors_slug_platform_idx');

            // Time-based analytics (partition-friendly)
            $table->index('created_at', 'visitors_created_at_idx');

            // Referrer analytics
            $table->index(['slug', 'referer'], 'visitors_slug_referer_idx');

            // IP-based lookups (for abuse detection)
            $table->index(['ip_address', 'created_at'], 'visitors_ip_created_at_idx');
        });

        // Create optimized views for common analytics queries
        $this->createAnalyticsViews();

        // Add database configuration optimizations
        $this->optimizeDatabaseConfiguration();
    }

    /**
     * Create optimized views for analytics
     */
    private function createAnalyticsViews(): void
    {
        // Daily click summary view
        DB::statement("
            CREATE OR REPLACE VIEW daily_clicks_summary AS
            SELECT
                slug,
                DATE(created_at) as click_date,
                COUNT(*) as total_clicks,
                COUNT(DISTINCT ip_address) as unique_visitors,
                COUNT(DISTINCT country) as countries_reached,
                GROUP_CONCAT(DISTINCT country SEPARATOR ',') as top_countries
            FROM visitors
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            GROUP BY slug, DATE(created_at)
            ORDER BY click_date DESC, total_clicks DESC
        ");

        // Geographic summary view
        DB::statement("
            CREATE OR REPLACE VIEW geographic_summary AS
            SELECT
                slug,
                country,
                city,
                COUNT(*) as clicks,
                AVG(latitude) as avg_lat,
                AVG(longitude) as avg_lng
            FROM visitors
            WHERE country IS NOT NULL
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY slug, country, city
            HAVING clicks > 1
            ORDER BY clicks DESC
        ");

        // Device/browser summary view
        DB::statement("
            CREATE OR REPLACE VIEW device_browser_summary AS
            SELECT
                slug,
                device,
                browser,
                platform,
                COUNT(*) as clicks
            FROM visitors
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY slug, device, browser, platform
            ORDER BY clicks DESC
        ");

        // Real-time link performance view
        DB::statement("
            CREATE OR REPLACE VIEW link_performance AS
            SELECT
                l.id,
                l.slug,
                l.title,
                l.url,
                l.user,
                l.created_at as link_created,
                COALESCE(v.total_clicks, 0) as total_clicks,
                COALESCE(v.today_clicks, 0) as today_clicks,
                COALESCE(v.week_clicks, 0) as week_clicks,
                COALESCE(v.month_clicks, 0) as month_clicks
            FROM links l
            LEFT JOIN (
                SELECT
                    slug,
                    COUNT(*) as total_clicks,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_clicks,
                    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_clicks,
                    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as month_clicks
                FROM visitors
                GROUP BY slug
            ) v ON l.slug = v.slug
            WHERE l.deleted_at IS NULL
        ");
    }

    /**
     * Add database configuration optimizations
     */
    private function optimizeDatabaseConfiguration(): void
    {
        // Set MySQL-specific optimizations for high-concurrency workloads
        // These would typically be set in my.cnf, but we can set session variables

        $optimizations = [
            // Connection and thread settings
            'SET GLOBAL max_connections = 1000',
            'SET GLOBAL innodb_thread_concurrency = 16',

            // Buffer pool for InnoDB (adjust based on server memory)
            'SET GLOBAL innodb_buffer_pool_size = 1073741824', // 1GB
            'SET GLOBAL innodb_log_file_size = 268435456', // 256MB

            // Query cache settings (if using MySQL 5.7 or earlier)
            'SET GLOBAL query_cache_size = 134217728', // 128MB
            'SET GLOBAL query_cache_type = ON',
            'SET GLOBAL query_cache_limit = 1048576', // 1MB

            // Connection pooling
            'SET GLOBAL innodb_old_blocks_pct = 37',
            'SET GLOBAL innodb_old_blocks_time = 1000',

            // Read-ahead settings
            'SET GLOBAL innodb_read_ahead_threshold = 56',
            'SET GLOBAL innodb_random_read_ahead = ON',

            // Flush settings for write performance
            'SET GLOBAL innodb_flush_log_at_trx_commit = 2',
            'SET GLOBAL innodb_flush_method = O_DIRECT',

            // Temporary table settings
            'SET GLOBAL tmp_table_size = 134217728', // 128MB
            'SET GLOBAL max_heap_table_size = 134217728', // 128MB
        ];

        // Note: These are session-level settings and would need to be set globally in production
        // In a real deployment, these would be configured in my.cnf or my.ini
        foreach ($optimizations as $query) {
            try {
                DB::statement($query);
            } catch (\Exception $e) {
                // Log but don't fail migration if settings can't be applied
                // (e.g., in SQLite or if user doesn't have privileges)
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop optimized views
        DB::statement('DROP VIEW IF EXISTS link_performance');
        DB::statement('DROP VIEW IF EXISTS device_browser_summary');
        DB::statement('DROP VIEW IF EXISTS geographic_summary');
        DB::statement('DROP VIEW IF EXISTS daily_clicks_summary');

        // Drop indexes from visitors table
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropIndex('visitors_slug_created_at_idx');
            $table->dropIndex('visitors_slug_country_idx');
            $table->dropIndex('visitors_slug_city_idx');
            $table->dropIndex('visitors_slug_device_idx');
            $table->dropIndex('visitors_slug_browser_idx');
            $table->dropIndex('visitors_slug_platform_idx');
            $table->dropIndex('visitors_created_at_idx');
            $table->dropIndex('visitors_slug_referer_idx');
            $table->dropIndex('visitors_ip_created_at_idx');
        });

        // Drop indexes from links table
        Schema::table('links', function (Blueprint $table) {
            $table->dropIndex('links_slug_idx');
            $table->dropIndex('links_user_idx');
            $table->dropIndex('links_user_disabled_idx');
            $table->dropIndex('links_user_created_at_idx');
            $table->dropIndex('links_deleted_at_idx');
            $table->dropIndex('links_active_created_idx');
        });

        // Drop indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_email_verified_idx');
            $table->dropIndex('users_google_id_idx');
            $table->dropIndex('users_facebook_id_idx');
            $table->dropIndex('users_created_at_idx');
        });
    }
};
