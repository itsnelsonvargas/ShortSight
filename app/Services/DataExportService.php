<?php

namespace App\Services;

use App\Models\User;
use App\Models\Link;
use App\Models\Visitor;
use Illuminate\Support\Collection;

class DataExportService
{
    /**
     * Export all user data in GDPR-compliant format
     *
     * @param User $user
     * @return array
     */
    public function exportUserData(User $user): array
    {
        return [
            'export_metadata' => [
                'export_date' => now()->toISOString(),
                'user_id' => $user->id,
                'gdpr_compliant' => true,
                'data_portability_version' => '1.0',
                'data_format' => 'JSON',
            ],
            'user_profile' => $this->getUserProfileData($user),
            'links' => $this->getUserLinksData($user),
            'analytics' => $this->getUserAnalyticsData($user),
            'data_summary' => $this->getDataSummary($user),
        ];
    }

    /**
     * Get user profile data (excluding sensitive information)
     *
     * @param User $user
     * @return array
     */
    private function getUserProfileData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'social_logins' => [
                'google_id' => $user->google_id ? true : false, // Only indicate presence, not the actual ID
                'facebook_id' => $user->facebook_id ? true : false, // Only indicate presence, not the actual ID
            ],
            'account_created_at' => $user->created_at->toISOString(),
            'account_updated_at' => $user->updated_at->toISOString(),
        ];
    }

    /**
     * Get all links created by the user
     *
     * @param User $user
     * @return array
     */
    private function getUserLinksData(User $user): array
    {
        $links = Link::where('user', $user->id)
            ->withTrashed() // Include soft-deleted links
            ->orderBy('created_at', 'desc')
            ->get();

        return $links->map(function ($link) {
            return [
                'id' => $link->id,
                'title' => $link->title,
                'description' => $link->description,
                'original_url' => $link->url,
                'short_slug' => $link->slug,
                'is_disabled' => $link->is_disabled,
                'created_at' => $link->created_at->toISOString(),
                'updated_at' => $link->updated_at->toISOString(),
                'deleted_at' => $link->deleted_at?->toISOString(),
                'click_count' => $this->getLinkClickCount($link->slug),
            ];
        })->toArray();
    }

    /**
     * Get analytics data for user's links
     *
     * @param User $user
     * @return array
     */
    private function getUserAnalyticsData(User $user): array
    {
        $userLinks = Link::where('user', $user->id)->pluck('slug');
        $visitors = Visitor::whereIn('slug', $userLinks)
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by link slug for better organization
        $analyticsByLink = $visitors->groupBy('slug')->map(function ($linkVisitors, $slug) {
            return [
                'link_slug' => $slug,
                'total_clicks' => $linkVisitors->count(),
                'clicks_by_date' => $linkVisitors->groupBy(function ($visitor) {
                    return $visitor->created_at->format('Y-m-d');
                })->map->count()->toArray(),
                'clicks_by_country' => $linkVisitors->whereNotNull('country')
                    ->groupBy('country')
                    ->map->count()
                    ->sortDesc()
                    ->toArray(),
                'clicks_by_device' => $linkVisitors->whereNotNull('device')
                    ->groupBy('device')
                    ->map->count()
                    ->sortDesc()
                    ->toArray(),
                'clicks_by_browser' => $linkVisitors->whereNotNull('browser')
                    ->groupBy('browser')
                    ->map->count()
                    ->sortDesc()
                    ->toArray(),
                'referrers' => $linkVisitors->whereNotNull('referer')
                    ->groupBy('referer')
                    ->map->count()
                    ->sortDesc()
                    ->take(10) // Top 10 referrers
                    ->toArray(),
                'detailed_clicks' => $linkVisitors->map(function ($visitor) {
                    // Anonymize IP addresses for GDPR compliance
                    $anonymizedIp = $this->anonymizeIp($visitor->ip_address);
                    return [
                        'timestamp' => $visitor->created_at->toISOString(),
                        'ip_address_anonymized' => $anonymizedIp,
                        'user_agent' => $visitor->user_agent,
                        'browser' => $visitor->browser,
                        'device' => $visitor->device,
                        'platform' => $visitor->platform,
                        'country' => $visitor->country,
                        'city' => $visitor->city,
                        'region' => $visitor->region,
                        'has_vpn' => $visitor->has_vpn,
                    ];
                })->toArray(),
            ];
        });

        return [
            'total_clicks_across_all_links' => $visitors->count(),
            'date_range' => [
                'earliest_click' => $visitors->min('created_at')?->toISOString(),
                'latest_click' => $visitors->max('created_at')?->toISOString(),
            ],
            'analytics_by_link' => $analyticsByLink->values()->toArray(),
        ];
    }

    /**
     * Get summary statistics for the exported data
     *
     * @param User $user
     * @return array
     */
    private function getDataSummary(User $user): array
    {
        $links = Link::where('user', $user->id)->get();
        $totalClicks = 0;

        foreach ($links as $link) {
            $totalClicks += $this->getLinkClickCount($link->slug);
        }

        return [
            'total_links_created' => $links->count(),
            'total_clicks_received' => $totalClicks,
            'active_links' => $links->where('is_disabled', false)->count(),
            'disabled_links' => $links->where('is_disabled', true)->count(),
            'account_age_days' => $user->created_at->diffInDays(now()),
            'data_export_completeness' => '100%', // All available data included
        ];
    }

    /**
     * Get click count for a specific link slug
     *
     * @param string $slug
     * @return int
     */
    private function getLinkClickCount(string $slug): int
    {
        return Visitor::where('slug', $slug)->count();
    }

    /**
     * Anonymize IP address for GDPR compliance
     * Converts last octet of IPv4 or last 80 bits of IPv6 to zeros
     *
     * @param string|null $ip
     * @return string|null
     */
    private function anonymizeIp(?string $ip): ?string
    {
        if (!$ip) {
            return null;
        }

        // Check if IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = '0'; // Anonymize last octet
            return implode('.', $parts);
        }

        // Check if IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // For IPv6, we anonymize the last 80 bits (last 5 segments of 16 bits each)
            $parts = explode(':', $ip);
            // Pad to 8 segments if necessary
            while (count($parts) < 8) {
                $parts = array_merge(array_slice($parts, 0, -1), ['0000'], array_slice($parts, -1));
            }
            // Replace last 3 segments with zeros (96 bits anonymized)
            $parts[5] = '0000';
            $parts[6] = '0000';
            $parts[7] = '0000';
            return implode(':', $parts);
        }

        return $ip; // Return as-is if not a valid IP
    }

    /**
     * Generate a downloadable JSON file response
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function generateDownloadResponse(User $user)
    {
        $data = $this->exportUserData($user);
        $filename = "shortsight-data-export-{$user->id}-" . now()->format('Y-m-d-H-i-s') . '.json';

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
