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
        // Create archive tables for old visitor data
        $this->createArchiveTables();

        // Create archiving procedures
        $this->createArchivingProcedures();

        // Create cleanup jobs
        $this->createCleanupJobs();
    }

    /**
     * Create archive tables for historical data
     */
    private function createArchiveTables(): void
    {
        // Archive table for visitors older than 1 year
        Schema::create('visitors_archive_1year', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('slug');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('device', 100)->nullable();
            $table->string('platform', 100)->nullable();
            $table->string('referer', 255)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->float('latitude', 10, 6)->nullable();
            $table->float('longitude', 10, 6)->nullable();
            $table->boolean('has_vpn')->default(false);
            $table->string('vpn', 45)->nullable();

            // Add indexes optimized for archive queries
            $table->index(['slug', 'created_at'], 'archive_slug_created_idx');
            $table->index('created_at', 'archive_created_at_idx');
        });

        // Archive table for visitors older than 2 years (compressed/summarized)
        Schema::create('visitors_archive_2year', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->date('date');
            $table->integer('total_clicks');
            $table->integer('unique_visitors');
            $table->json('countries')->nullable(); // Top countries
            $table->json('devices')->nullable(); // Device breakdown
            $table->json('browsers')->nullable(); // Browser breakdown
            $table->json('referrers')->nullable(); // Top referrers
            $table->timestamps();

            $table->unique(['slug', 'date'], 'archive_summary_unique_idx');
            $table->index('date', 'archive_summary_date_idx');
        });

        // Summary table for all-time statistics
        Schema::create('link_statistics_summary', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->bigInteger('total_clicks')->default(0);
            $table->bigInteger('unique_visitors')->default(0);
            $table->integer('countries_reached')->default(0);
            $table->integer('cities_reached')->default(0);
            $table->json('top_countries')->nullable();
            $table->json('top_cities')->nullable();
            $table->json('device_breakdown')->nullable();
            $table->json('browser_breakdown')->nullable();
            $table->date('first_click_date')->nullable();
            $table->date('last_click_date')->nullable();
            $table->timestamps();

            $table->index(['total_clicks', 'updated_at'], 'stats_clicks_updated_idx');
            $table->index('updated_at', 'stats_updated_at_idx');
        });
    }

    /**
     * Create archiving procedures
     */
    private function createArchivingProcedures(): void
    {
        // Procedure to archive visitors older than 1 year
        $archive1YearSql = "
            CREATE PROCEDURE archive_old_visitors_1year()
            BEGIN
                DECLARE done INT DEFAULT FALSE;
                DECLARE batch_size INT DEFAULT 10000;
                DECLARE archived_count INT DEFAULT 0;

                -- Archive in batches to avoid locking tables
                WHILE archived_count < 100000 DO
                    -- Insert old records to archive table
                    INSERT INTO visitors_archive_1year (
                        id, created_at, updated_at, slug, ip_address, user_agent,
                        browser, device, platform, referer, country, city,
                        region, postal_code, latitude, longitude, has_vpn, vpn
                    )
                    SELECT
                        id, created_at, updated_at, slug, ip_address, user_agent,
                        browser, device, platform, referer, country, city,
                        region, postal_code, latitude, longitude, has_vpn, vpn
                    FROM visitors
                    WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)
                    LIMIT batch_size;

                    -- Delete archived records
                    DELETE FROM visitors
                    WHERE id IN (
                        SELECT id FROM visitors_archive_1year
                        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
                        AND created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)
                    )
                    LIMIT batch_size;

                    SET archived_count = archived_count + ROW_COUNT();

                    IF ROW_COUNT() < batch_size THEN
                        LEAVE;
                    END IF;
                END WHILE;

                SELECT CONCAT('Archived ', archived_count, ' visitor records older than 1 year') as result;
            END
        ";

        // Procedure to create summarized archives for data older than 2 years
        $archive2YearSql = "
            CREATE PROCEDURE archive_old_visitors_2year()
            BEGIN
                -- Create summarized data for visitors older than 2 years
                INSERT INTO visitors_archive_2year (slug, date, total_clicks, unique_visitors, countries, devices, browsers, referrers, created_at, updated_at)
                SELECT
                    slug,
                    DATE(created_at) as date,
                    COUNT(*) as total_clicks,
                    COUNT(DISTINCT ip_address) as unique_visitors,
                    JSON_ARRAYAGG(DISTINCT country) as countries,
                    JSON_OBJECTAGG(device, COUNT(*)) as devices,
                    JSON_OBJECTAGG(browser, COUNT(*)) as browsers,
                    JSON_OBJECTAGG(referer, COUNT(*)) as referrers,
                    NOW() as created_at,
                    NOW() as updated_at
                FROM visitors_archive_1year
                WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR)
                GROUP BY slug, DATE(created_at);

                -- Remove the detailed records after summarization
                DELETE FROM visitors_archive_1year
                WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR);

                SELECT 'Created summarized archives for data older than 2 years' as result;
            END
        ";

        // Procedure to update link statistics summary
        $updateStatsSql = "
            CREATE PROCEDURE update_link_statistics_summary()
            BEGIN
                -- Update existing records
                UPDATE link_statistics_summary lss
                INNER JOIN (
                    SELECT
                        v.slug,
                        COUNT(*) as total_clicks,
                        COUNT(DISTINCT v.ip_address) as unique_visitors,
                        COUNT(DISTINCT v.country) as countries_reached,
                        COUNT(DISTINCT CONCAT(v.city, ',', v.country)) as cities_reached,
                        JSON_ARRAYAGG(DISTINCT v.country ORDER BY COUNT(*) DESC LIMIT 10) as top_countries,
                        JSON_ARRAYAGG(DISTINCT CONCAT(v.city, ',', v.country) ORDER BY COUNT(*) DESC LIMIT 10) as top_cities,
                        JSON_OBJECTAGG(v.device, COUNT(*)) as device_breakdown,
                        JSON_OBJECTAGG(v.browser, COUNT(*)) as browser_breakdown,
                        MIN(DATE(v.created_at)) as first_click_date,
                        MAX(DATE(v.created_at)) as last_click_date
                    FROM visitors v
                    GROUP BY v.slug
                ) stats ON lss.slug = stats.slug
                SET
                    lss.total_clicks = stats.total_clicks,
                    lss.unique_visitors = stats.unique_visitors,
                    lss.countries_reached = stats.countries_reached,
                    lss.cities_reached = stats.cities_reached,
                    lss.top_countries = stats.top_countries,
                    lss.top_cities = stats.top_cities,
                    lss.device_breakdown = stats.device_breakdown,
                    lss.browser_breakdown = stats.browser_breakdown,
                    lss.first_click_date = stats.first_click_date,
                    lss.last_click_date = stats.last_click_date,
                    lss.updated_at = NOW();

                -- Insert new records for links that don't exist in summary
                INSERT INTO link_statistics_summary (
                    slug, total_clicks, unique_visitors, countries_reached, cities_reached,
                    top_countries, top_cities, device_breakdown, browser_breakdown,
                    first_click_date, last_click_date, created_at, updated_at
                )
                SELECT
                    stats.slug,
                    stats.total_clicks,
                    stats.unique_visitors,
                    stats.countries_reached,
                    stats.cities_reached,
                    stats.top_countries,
                    stats.top_cities,
                    stats.device_breakdown,
                    stats.browser_breakdown,
                    stats.first_click_date,
                    stats.last_click_date,
                    NOW(),
                    NOW()
                FROM (
                    SELECT
                        v.slug,
                        COUNT(*) as total_clicks,
                        COUNT(DISTINCT v.ip_address) as unique_visitors,
                        COUNT(DISTINCT v.country) as countries_reached,
                        COUNT(DISTINCT CONCAT(v.city, ',', v.country)) as cities_reached,
                        JSON_ARRAYAGG(DISTINCT v.country ORDER BY COUNT(*) DESC LIMIT 10) as top_countries,
                        JSON_ARRAYAGG(DISTINCT CONCAT(v.city, ',', v.country) ORDER BY COUNT(*) DESC LIMIT 10) as top_cities,
                        JSON_OBJECTAGG(v.device, COUNT(*)) as device_breakdown,
                        JSON_OBJECTAGG(v.browser, COUNT(*)) as browser_breakdown,
                        MIN(DATE(v.created_at)) as first_click_date,
                        MAX(DATE(v.created_at)) as last_click_date
                    FROM visitors v
                    GROUP BY v.slug
                ) stats
                LEFT JOIN link_statistics_summary lss ON lss.slug = stats.slug
                WHERE lss.id IS NULL;

                SELECT 'Updated link statistics summary' as result;
            END
        ";

        try {
            DB::statement('DROP PROCEDURE IF EXISTS archive_old_visitors_1year');
            DB::statement($archive1YearSql);

            DB::statement('DROP PROCEDURE IF EXISTS archive_old_visitors_2year');
            DB::statement($archive2YearSql);

            DB::statement('DROP PROCEDURE IF EXISTS update_link_statistics_summary');
            DB::statement($updateStatsSql);
        } catch (\Exception $e) {
            // Stored procedures might not be supported on all databases
        }
    }

    /**
     * Create cleanup jobs and scheduled tasks
     */
    private function createCleanupJobs(): void
    {
        // Create a command that can be scheduled to run archiving
        // This would typically be called from a Laravel command or scheduled job
        $this->createArchivingCommand();
    }

    /**
     * Create archiving command
     */
    private function createArchivingCommand(): void
    {
        // This would create a Laravel command for archiving
        // For now, we'll create the basic structure

        $commandContent = <<<'EOD'
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ArchiveOldVisitorData extends Command
{
    protected $signature = 'visitors:archive {--dry-run : Show what would be archived without actually doing it}';
    protected $description = 'Archive old visitor data to improve performance';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No data will be modified');
        }

        // Check how many records are old enough to archive
        $oneYearOld = DB::table('visitors')
            ->where('created_at', '<', now()->subYear())
            ->count();

        $twoYearsOld = DB::table('visitors')
            ->where('created_at', '<', now()->subYears(2))
            ->count();

        $this->info("Records older than 1 year: {$oneYearOld}");
        $this->info("Records older than 2 years: {$twoYearsOld}");

        if (!$dryRun && $this->confirm('Proceed with archiving?')) {
            // Archive 1-year-old data
            if ($oneYearOld > 0) {
                $this->info('Archiving 1-year-old visitor data...');
                DB::statement('CALL archive_old_visitors_1year()');
                $this->info('1-year archiving completed');
            }

            // Archive 2-year-old data
            if ($twoYearsOld > 0) {
                $this->info('Creating summarized archives for 2-year-old data...');
                DB::statement('CALL archive_old_visitors_2year()');
                $this->info('2-year archiving completed');
            }

            // Update statistics summary
            $this->info('Updating link statistics summary...');
            DB::statement('CALL update_link_statistics_summary()');
            $this->info('Statistics update completed');

            $this->info('Archiving process completed successfully!');
        }
    }
}
EOD;

        // Save the command file
        file_put_contents(app_path('Console/Commands/ArchiveOldVisitorData.php'), $commandContent);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            // Drop procedures
            DB::statement('DROP PROCEDURE IF EXISTS archive_old_visitors_1year');
            DB::statement('DROP PROCEDURE IF EXISTS archive_old_visitors_2year');
            DB::statement('DROP PROCEDURE IF EXISTS update_link_statistics_summary');

            // Drop archive tables
            Schema::dropIfExists('link_statistics_summary');
            Schema::dropIfExists('visitors_archive_2year');
            Schema::dropIfExists('visitors_archive_1year');

            // Remove the command file
            $commandPath = app_path('Console/Commands/ArchiveOldVisitorData.php');
            if (file_exists($commandPath)) {
                unlink($commandPath);
            }
        } catch (\Exception $e) {
            // Handle gracefully
        }
    }
};
