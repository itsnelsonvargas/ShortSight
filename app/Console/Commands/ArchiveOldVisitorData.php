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