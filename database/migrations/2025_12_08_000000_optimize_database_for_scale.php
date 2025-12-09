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
        // Optimize users table indexes (SQLite compatible)
        $this->createIndexIfNotExists('users', ['email', 'email_verified_at'], 'users_email_verified_idx');
        $this->createIndexIfNotExists('users', 'google_id', 'users_google_id_idx');
        $this->createIndexIfNotExists('users', 'facebook_id', 'users_facebook_id_idx');
        $this->createIndexIfNotExists('users', 'created_at', 'users_created_at_idx');

        // Optimize links table indexes
        $this->createIndexIfNotExists('links', 'slug', 'links_slug_idx');
        $this->createIndexIfNotExists('links', 'user', 'links_user_idx');
        $this->createIndexIfNotExists('links', ['user', 'is_disabled'], 'links_user_disabled_idx');
        $this->createIndexIfNotExists('links', ['user', 'created_at'], 'links_user_created_at_idx');
        $this->createIndexIfNotExists('links', 'deleted_at', 'links_deleted_at_idx');
        $this->createIndexIfNotExists('links', ['is_disabled', 'created_at'], 'links_active_created_idx');

        // Optimize visitors table for massive scale
        $this->createIndexIfNotExists('visitors', ['slug', 'created_at'], 'visitors_slug_created_at_idx');
        $this->createIndexIfNotExists('visitors', ['slug', 'country'], 'visitors_slug_country_idx');
        $this->createIndexIfNotExists('visitors', ['slug', 'city'], 'visitors_slug_city_idx');
        $this->createIndexIfNotExists('visitors', ['slug', 'device'], 'visitors_slug_device_idx');
        $this->createIndexIfNotExists('visitors', ['slug', 'browser'], 'visitors_slug_browser_idx');
        $this->createIndexIfNotExists('visitors', ['slug', 'platform'], 'visitors_slug_platform_idx');
        $this->createIndexIfNotExists('visitors', 'created_at', 'visitors_created_at_idx');
        $this->createIndexIfNotExists('visitors', ['slug', 'referer'], 'visitors_slug_referer_idx');
        $this->createIndexIfNotExists('visitors', ['ip_address', 'created_at'], 'visitors_ip_created_at_idx');

        // Create optimized views for common analytics queries
        $this->createAnalyticsViews();

        // Add database configuration optimizations
        $this->optimizeDatabaseConfiguration();
    }

    /**
     * Create index if it doesn't exist (SQLite compatible)
     */
    private function createIndexIfNotExists(string $table, array|string $columns, string $indexName): void
    {
        try {
            $indexes = DB::select("PRAGMA index_list({$table})");
            $existingIndexes = array_column($indexes, 'name');

            if (!in_array($indexName, $existingIndexes)) {
                Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                    $table->index($columns, $indexName);
                });
            }
        } catch (\Exception $e) {
            // If we can't check existing indexes, try to create anyway
            // SQLite will throw an error if it exists, which we'll catch
            try {
                Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                    $table->index($columns, $indexName);
                });
            } catch (\Exception $e) {
                // Index likely already exists, continue
            }
        }
    }

    /**
     * Create optimized views for analytics
     */
    private function createAnalyticsViews(): void
    {
        // Drop existing views if they exist (SQLite compatible)
        try {
            DB::statement('DROP VIEW IF EXISTS daily_clicks_summary');
            DB::statement('DROP VIEW IF EXISTS geographic_summary');
            DB::statement('DROP VIEW IF EXISTS device_browser_summary');
            DB::statement('DROP VIEW IF EXISTS link_performance');
        } catch (\Exception $e) {
            // Views might not exist, continue
        }

        // Daily click summary view (SQLite compatible)
        DB::statement("
            CREATE VIEW daily_clicks_summary AS
            SELECT
                slug,
                DATE(created_at) as click_date,
                COUNT(*) as total_clicks,
                COUNT(DISTINCT ip_address) as unique_visitors,
                COUNT(DISTINCT country) as countries_reached
            FROM visitors
            WHERE created_at >= datetime('now', '-90 days')
            GROUP BY slug, DATE(created_at)
            ORDER BY click_date DESC, total_clicks DESC
        ");

        // Geographic summary view
        DB::statement("
            CREATE VIEW geographic_summary AS
            SELECT
                slug,
                country,
                city,
                COUNT(*) as clicks,
                AVG(latitude) as avg_lat,
                AVG(longitude) as avg_lng
            FROM visitors
            WHERE country IS NOT NULL
            AND created_at >= datetime('now', '-30 days')
            GROUP BY slug, country, city
            HAVING clicks > 1
            ORDER BY clicks DESC
        ");

        // Device/browser summary view
        DB::statement("
            CREATE VIEW device_browser_summary AS
            SELECT
                slug,
                device,
                browser,
                platform,
                COUNT(*) as clicks
            FROM visitors
            WHERE created_at >= datetime('now', '-7 days')
            GROUP BY slug, device, browser, platform
            ORDER BY clicks DESC
        ");

        // Real-time link performance view
        DB::statement("
            CREATE VIEW link_performance AS
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
                    SUM(CASE WHEN DATE(created_at) = DATE('now') THEN 1 ELSE 0 END) as today_clicks,
                    SUM(CASE WHEN created_at >= datetime('now', '-7 days') THEN 1 ELSE 0 END) as week_clicks,
                    SUM(CASE WHEN created_at >= datetime('now', '-30 days') THEN 1 ELSE 0 END) as month_clicks
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
        // SQLite-specific optimizations for better performance
        // Enable WAL mode for better concurrent read/write performance
        try {
            DB::statement('PRAGMA journal_mode = WAL');
            DB::statement('PRAGMA synchronous = NORMAL');
            DB::statement('PRAGMA cache_size = 1000000'); // ~1GB cache
            DB::statement('PRAGMA temp_store = memory');
            DB::statement('PRAGMA mmap_size = 268435456'); // 256MB memory map
        } catch (\Exception $e) {
            // SQLite pragmas might not be supported in all contexts
            // Continue without failing the migration
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
