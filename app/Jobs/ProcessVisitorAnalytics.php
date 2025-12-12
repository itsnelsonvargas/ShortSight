<?php

namespace App\Jobs;

use App\Models\Visitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessVisitorAnalytics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Basic visitor snapshot captured during the request.
     *
     * @var array<string, mixed>
     */
    protected array $visitorData;

    /**
     * The slug that was accessed.
     */
    protected string $slug;

    /**
     * Create a new job instance.
     *
     * @param string $slug
     * @param array<string, mixed> $visitorData
     */
    public function __construct(string $slug, array $visitorData)
    {
        $this->slug = $slug;
        $this->visitorData = $visitorData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $visitorInfo = getVisitorInfoFromData($this->visitorData);

            Visitor::create([
                'slug' => $this->slug,
                'ip_address' => $visitorInfo['ip_address'],
                'user_agent' => $visitorInfo['user_agent'],
                'browser' => $visitorInfo['browser'],
                'device' => $visitorInfo['device'],
                'platform' => $visitorInfo['platform'],
                'referer' => $visitorInfo['referer'],
                'country' => $visitorInfo['location']['country'] ?? null,
                'city' => $visitorInfo['location']['city'] ?? null,
                'region' => $visitorInfo['location']['region'] ?? null,
                'postal_code' => $visitorInfo['location']['postal_code'] ?? null,
                'latitude' => $visitorInfo['location']['latitude'] ?? null,
                'longitude' => $visitorInfo['location']['longitude'] ?? null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to process visitor analytics', [
                'slug' => $this->slug,
                'error' => $e->getMessage(),
                'visitor_data' => $this->visitorData,
            ]);

            // Re-throw to let Laravel mark the job as failed
            throw $e;
        }
    }
}

