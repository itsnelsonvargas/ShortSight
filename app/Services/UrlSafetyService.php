<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UrlSafetyService
{
    protected $apiKey;
    protected $config;

    public function __construct()
    {
        $this->apiKey = config('services.google_safe_browsing.key');
        $this->config = config('url_safety');
    }

    /**
     * Comprehensive URL validation
     * Returns array with validation results
     */
    public function validateUrl($url): array
    {
        $results = [
            'is_safe' => true,
            'errors' => [],
            'warnings' => [],
        ];

        // Basic URL format validation
        if (!$this->isValidUrlFormat($url)) {
            $results['is_safe'] = false;
            $results['errors'][] = 'Invalid URL format';
            return $results;
        }

        // Check URL length
        if ($this->config['enable_url_length_check'] && !$this->isValidUrlLength($url)) {
            $results['is_safe'] = false;
            $results['errors'][] = 'URL exceeds maximum allowed length';
        }

        // Check for private IPs and localhost
        if ($this->isPrivateOrLocalAddress($url)) {
            $results['is_safe'] = false;
            $results['errors'][] = 'Links to private networks or localhost are not allowed';
        }

        // Check unicode domains (warning only)
        if ($this->config['enable_unicode_check'] && $this->hasUnicodeDomain($url)) {
            $results['warnings'][] = 'URL contains unicode characters in domain';
        }

        // Check domain blacklist
        if ($this->config['enable_domain_blacklist'] && $this->isDomainBlacklisted($url)) {
            $results['is_safe'] = false;
            $results['errors'][] = 'Domain is blacklisted';
        }

        // Check malicious patterns
        if ($this->config['enable_pattern_detection']) {
            $patternCheck = $this->checkMaliciousPatterns($url);
            if (!$patternCheck['safe']) {
                $results['is_safe'] = false;
                $results['errors'] = array_merge($results['errors'], $patternCheck['errors']);
            }
        }

        // Check content type (only if URL is accessible and HTTPS)
        if ($this->config['enable_content_type_check']) {
            $contentCheck = $this->checkContentType($url);
            if (!$contentCheck['safe']) {
                $results['is_safe'] = false;
                $results['errors'] = array_merge($results['errors'], $contentCheck['errors']);
            }
        }

        // Check Google Safe Browsing (external API check)
        if ($this->config['enable_google_safe_browsing'] && $this->isMalicious($url)) {
            $results['is_safe'] = false;
            $results['errors'][] = 'URL flagged by Google Safe Browsing';
        }

        return $results;
    }

    /**
     * Check if URL is malicious using Google Safe Browsing API
     */
    public function isMalicious($url): bool
    {
        $response = Http::post("https://safebrowsing.googleapis.com/v4/threatMatches:find?key={$this->apiKey}", [
            'client' => [
                'clientId'          => 'shortsight-url-shortener',
                'clientVersion'     => '1.0',
            ],
            'threatInfo' => [
                'threatTypes'       => ['MALWARE', 'SOCIAL_ENGINEERING', 'UNWANTED_SOFTWARE'],
                'platformTypes'     => ['ANY_PLATFORM'],
                'threatEntryTypes'  => ['URL'],
                'threatEntries'     => [
                    ['url' => $url],
                ],
            ],
        ]);

        if (!$response->successful()) {
            Log::warning('Google Safe Browsing API request failed', [
                'url' => $url,
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return false; // fallback if API fails
        }

        return isset($response['matches']) && !empty($response['matches']);
    }

    /**
     * Check if domain is in blacklist
     */
    protected function isDomainBlacklisted($url): bool
    {
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['host'])) {
            return false;
        }

        $domain = strtolower($parsed['host']);

        // Remove www. prefix for comparison
        $domain = preg_replace('/^www\./', '', $domain);

        return in_array($domain, $this->config['domain_blacklist']);
    }

    /**
     * Check URL for malicious patterns
     */
    protected function checkMaliciousPatterns($url): array
    {
        $results = [
            'safe' => true,
            'errors' => [],
        ];

        foreach ($this->config['malicious_patterns'] as $pattern) {
            if (preg_match($pattern, $url)) {
                $results['safe'] = false;
                $results['errors'][] = 'URL contains suspicious pattern';
                break; // One match is enough
            }
        }

        return $results;
    }

    /**
     * Check content type of the URL
     */
    protected function checkContentType($url): array
    {
        $results = [
            'safe' => true,
            'errors' => [],
        ];

        try {
            // Only check content type for HTTP/HTTPS URLs
            if (!preg_match('/^https?:\/\//i', $url)) {
                return $results;
            }

            // Use HEAD request to check content type without downloading
            $response = Http::timeout($this->config['content_check_timeout'])->withHeaders([
                'User-Agent' => 'ShortSight-URL-Validator/1.0'
            ])->head($url);

            if ($response->successful()) {
                $contentType = $response->header('Content-Type');

                if ($contentType) {
                    // Extract MIME type (remove charset and other parameters)
                    $mimeType = explode(';', $contentType)[0];
                    $mimeType = trim(strtolower($mimeType));

                    if (in_array($mimeType, $this->config['content_type_blacklist'])) {
                        $results['safe'] = false;
                        $results['errors'][] = "Blocked content type: {$mimeType}";
                    }
                }
            }
        } catch (\Exception $e) {
            // If content type check fails, log but don't block
            Log::info('Content type check failed for URL', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Add domain to blacklist (admin function)
     * Note: In production, this should update a database table and invalidate cache
     */
    public function addToDomainBlacklist(string $domain): bool
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('/^www\./', '', $domain);

        if (in_array($domain, $this->config['domain_blacklist'])) {
            return false; // Already exists
        }

        // In a real implementation, this would update a database
        // For now, we'll just log the action
        Log::info('Domain added to blacklist', ['domain' => $domain]);

        // Invalidate relevant caches
        $cacheService = app(RedisCacheService::class);
        $cacheService->invalidateDomainBlacklistCache();

        return true;
    }

    /**
     * Remove domain from blacklist (admin function)
     * Note: In production, this should update a database table and invalidate cache
     */
    public function removeFromDomainBlacklist(string $domain): bool
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('/^www\./', '', $domain);

        if (!in_array($domain, $this->config['domain_blacklist'])) {
            return false; // Doesn't exist
        }

        // In a real implementation, this would update a database
        Log::info('Domain removed from blacklist', ['domain' => $domain]);

        // Invalidate relevant caches
        $cacheService = app(RedisCacheService::class);
        $cacheService->invalidateDomainBlacklistCache();

        return true;
    }

    /**
     * Add pattern to malicious patterns list
     */
    public function addMaliciousPattern(string $pattern): bool
    {
        if (in_array($pattern, $this->config['malicious_patterns'])) {
            return false; // Already exists
        }

        // In a real implementation, this would update a database
        Log::info('Malicious pattern added', ['pattern' => $pattern]);

        // Invalidate URL validation caches
        $cacheService = app(RedisCacheService::class);
        // Clear all URL validation caches since patterns affect all URLs
        Cache::store('redis')->forget('url_validation:*');

        return true;
    }

    /**
     * Remove pattern from malicious patterns list
     */
    public function removeMaliciousPattern(string $pattern): bool
    {
        if (!in_array($pattern, $this->config['malicious_patterns'])) {
            return false; // Doesn't exist
        }

        // In a real implementation, this would update a database
        Log::info('Malicious pattern removed', ['pattern' => $pattern]);

        // Invalidate URL validation caches
        $cacheService = app(RedisCacheService::class);
        // Clear all URL validation caches since patterns affect all URLs
        Cache::store('redis')->forget('url_validation:*');

        return true;
    }

    /**
     * Get current domain blacklist
     */
    public function getDomainBlacklist(): array
    {
        return $this->config['domain_blacklist'];
    }

    /**
     * Check if URL has valid format
     */
    protected function isValidUrlFormat($url): bool
    {
        // Basic URL validation using filter_var
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Additional checks for URL structure
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['scheme']) || !isset($parsed['host'])) {
            return false;
        }

        // Only allow http and https schemes
        if (!in_array(strtolower($parsed['scheme']), ['http', 'https'])) {
            return false;
        }

        return true;
    }

    /**
     * Check if URL length is within acceptable limits
     */
    protected function isValidUrlLength($url): bool
    {
        return strlen($url) <= $this->config['max_url_length'];
    }

    /**
     * Check if URL points to private network or localhost
     */
    protected function isPrivateOrLocalAddress($url): bool
    {
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['host'])) {
            return false;
        }

        $host = strtolower($parsed['host']);

        // Check for localhost variations
        if ($this->config['block_localhost'] && in_array($host, ['localhost', '127.0.0.1', '::1'])) {
            return true;
        }

        // Check for private IP ranges
        if ($this->config['block_private_ips']) {
            $ip = gethostbyname($host);

            // Check IPv4 ranges
            foreach ($this->config['private_ip_ranges'] as $range) {
                if ($this->isIpInRange($ip, $range)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if an IP address is within a CIDR range
     */
    protected function isIpInRange($ip, $range): bool
    {
        list($subnet, $mask) = explode('/', $range);

        // Convert IP addresses to long integers
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        // Calculate the network mask
        $maskLong = -1 << (32 - (int)$mask);

        // Check if IP is in the subnet
        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    /**
     * Check if domain contains unicode characters
     */
    protected function hasUnicodeDomain($url): bool
    {
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['host'])) {
            return false;
        }

        $domain = $parsed['host'];

        // Check for non-ASCII characters
        return !mb_check_encoding($domain, 'ASCII');
    }

    /**
     * Enhanced content type checking with additional security
     */
    protected function checkContentType($url): array
    {
        $results = [
            'safe' => true,
            'errors' => [],
        ];

        try {
            // Only check content type for HTTP/HTTPS URLs
            if (!preg_match('/^https?:\/\//i', $url)) {
                return $results;
            }

            // Skip HTTP content checks if configured
            if (!$this->config['allow_http_content_check'] && preg_match('/^http:\/\//i', $url)) {
                $results['warnings'] = ['Content type check skipped for HTTP URL'];
                return $results;
            }

            // Use HEAD request to check content type without downloading
            $response = Http::timeout($this->config['content_check_timeout'])
                ->withHeaders([
                    'User-Agent' => 'ShortSight-URL-Validator/1.0',
                    'Accept' => '*/*',
                ])
                ->head($url);

            if ($response->successful()) {
                $contentType = $response->header('Content-Type');

                if ($contentType) {
                    // Extract MIME type (remove charset and other parameters)
                    $mimeType = explode(';', $contentType)[0];
                    $mimeType = trim(strtolower($mimeType));

                    // Check against blacklist
                    if (in_array($mimeType, $this->config['content_type_blacklist'])) {
                        $results['safe'] = false;
                        $results['errors'][] = "Blocked content type: {$mimeType}";
                    }

                    // Additional checks for suspicious content types
                    if ($this->isSuspiciousContentType($mimeType)) {
                        $results['safe'] = false;
                        $results['errors'][] = "Suspicious content type: {$mimeType}";
                    }
                } else {
                    // No content type header - suspicious
                    $results['warnings'] = ['No Content-Type header received'];
                }
            } else {
                // If HEAD fails, try a lightweight GET request
                $response = Http::timeout($this->config['content_check_timeout'])
                    ->withHeaders([
                        'User-Agent' => 'ShortSight-URL-Validator/1.0',
                        'Range' => 'bytes=0-1023', // Only get first 1KB
                    ])
                    ->get($url);

                if ($response->successful()) {
                    $contentType = $response->header('Content-Type');
                    if ($contentType) {
                        $mimeType = explode(';', $contentType)[0];
                        $mimeType = trim(strtolower($mimeType));

                        if (in_array($mimeType, $this->config['content_type_blacklist'])) {
                            $results['safe'] = false;
                            $results['errors'][] = "Blocked content type: {$mimeType}";
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // If content type check fails, log but don't block (fail-safe approach)
            Log::info('Content type check failed for URL', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            $results['warnings'] = ['Content type check failed - proceeding with caution'];
        }

        return $results;
    }

    /**
     * Check if content type is suspicious (additional security layer)
     */
    protected function isSuspiciousContentType($mimeType): bool
    {
        // Check for executable content in web contexts
        $suspiciousTypes = [
            'application/x-msdownload',
            'application/x-executable',
            'application/x-dosexec',
            'application/octet-stream',
        ];

        return in_array($mimeType, $suspiciousTypes);
    }
}
