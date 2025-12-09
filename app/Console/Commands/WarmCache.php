<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RedisCacheService;
use App\Models\Link;
use App\Models\Visitor;
use App\Services\UrlSafetyService;

class WarmCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm {--force : Force cache warming even if cache is not empty}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up Redis cache with frequently accessed data';

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
        $this->info('Starting cache warming process...');

        $force = $this->option('force');

        if (!$force) {
            $cacheStats = $this->cacheService->getCacheStats();
            if (!$cacheStats['redis_connected']) {
                $this->error('Redis is not connected. Please check your Redis configuration.');
                return 1;
            }
        }

        $this->info('Warming up link metadata cache...');
        $this->warmLinkMetadata();

        $this->info('Warming up URL safety cache...');
        $this->warmUrlSafety();

        $this->info('Warming up analytics cache...');
        $this->warmAnalytics();

        $this->info('Cache warming completed successfully!');
        return 0;
    }

    /**
     * Warm up link metadata cache
     */
    protected function warmLinkMetadata(): void
    {
        $links = Link::all();
        $progressBar = $this->output->createProgressBar($links->count());
        $progressBar->start();

        foreach ($links as $link) {
            $this->cacheService->cacheSlugLookup($link->slug, $link);
            $this->cacheService->cacheLinkMetadata($link);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Cached {$links->count()} link metadata entries");
    }

    /**
     * Warm up URL safety cache
     */
    protected function warmUrlSafety(): void
    {
        $urls = Link::distinct('url')->pluck('url')->take(1000); // Limit to prevent API rate limits
        $progressBar = $this->output->createProgressBar($urls->count());
        $progressBar->start();

        $urlSafetyService = new UrlSafetyService();

        foreach ($urls as $url) {
            // Check if already cached
            $cached = $this->cacheService->getCachedUrlSafety($url);

            if ($cached === null) {
                try {
                    $isMalicious = $urlSafetyService->isMalicious($url);
                    $this->cacheService->cacheUrlSafety($url, !$isMalicious);
                } catch (\Exception $e) {
                    $this->warn("Failed to check URL safety for: {$url}");
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Processed {$urls->count()} URL safety checks");
    }

    /**
     * Warm up analytics cache
     */
    protected function warmAnalytics(): void
    {
        $links = Link::select('slug')->get();
        $progressBar = $this->output->createProgressBar($links->count());
        $progressBar->start();

        foreach ($links as $link) {
            $clickCount = Visitor::where('slug', $link->slug)->count();
            $this->cacheService->cacheLinkClickCount($link->slug, $clickCount);

            // Cache basic analytics data
            $analytics = [
                'total_clicks' => $clickCount,
                'last_updated' => now()->toISOString(),
            ];
            $this->cacheService->cacheAnalyticsData($link->slug, $analytics);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Cached analytics for {$links->count()} links");
    }
}
