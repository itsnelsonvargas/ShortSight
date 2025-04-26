<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class UrlSafetyService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google_safe_browsing.key');
    }

    public function isMalicious($url): bool
    {
        $response = Http::post("https://safebrowsing.googleapis.com/v4/threatMatches:find?key={$this->apiKey}", [
            'client' => [
                'clientId'          => 'your-app',
                'clientVersion'     => '1.0',
            ],
            'threatInfo' => [
                'threatTypes'       => ['MALWARE', 'SOCIAL_ENGINEERING'],
                'platformTypes'     => ['ANY_PLATFORM'],
                'threatEntryTypes'  => ['URL'],
                'threatEntries'     => [
                    ['url' => $url],
                ],
            ],
        ]);

        // dd($response->json()); // Dump and die to check the actual response format
        // FOR TESTING:
        // Malicious URL: www.testsafebrowsing.appspot.com/s/malware.html
        
        if (!$response->successful()) {
            return false; // fallback if API fails
        }

        return isset($response['matches']) && !empty($response['matches']);
    }
}
