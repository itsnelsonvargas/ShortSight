<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Link;
use Illuminate\Support\Facades\Log;

class CleanupExpiredLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'links:cleanup-expired {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired links that have auto-delete enabled';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No links will be actually deleted');
        }

        // Find expired links with auto-delete enabled
        $expiredLinks = Link::expired()
            ->where('auto_delete_expired', true)
            ->get();

        if ($expiredLinks->isEmpty()) {
            $this->info('✅ No expired links found that need cleanup');
            return;
        }

        $this->info("Found {$expiredLinks->count()} expired links with auto-delete enabled");

        if ($dryRun) {
            $this->table(
                ['ID', 'Slug', 'URL', 'Expired At'],
                $expiredLinks->map(function ($link) {
                    return [
                        $link->id,
                        $link->slug,
                        $link->url,
                        $link->expires_at->format('Y-m-d H:i:s'),
                    ];
                })->toArray()
            );
            $this->info('🔍 Dry run completed. Use without --dry-run to actually delete these links.');
            return;
        }

        // Perform the actual cleanup
        $deletedCount = 0;
        $errors = [];

        foreach ($expiredLinks as $link) {
            try {
                $link->delete();
                $deletedCount++;
                $this->line("🗑️  Deleted expired link: {$link->slug}");
            } catch (\Exception $e) {
                $errors[] = "Failed to delete link {$link->slug}: {$e->getMessage()}";
                Log::error('Failed to delete expired link', [
                    'link_id' => $link->id,
                    'slug' => $link->slug,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Summary
        $this->info("✅ Cleanup completed: {$deletedCount} links deleted");

        if (!empty($errors)) {
            $this->error('Some errors occurred:');
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
        }

        Log::info('Expired links cleanup completed', [
            'deleted_count' => $deletedCount,
            'errors_count' => count($errors),
            'dry_run' => $dryRun
        ]);
    }
}
