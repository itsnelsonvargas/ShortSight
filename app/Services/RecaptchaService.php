<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    protected $secretKey;
    protected $scoreThreshold;

    public function __construct()
    {
        $this->secretKey = config('services.recaptcha.secret_key');
        $this->scoreThreshold = config('services.recaptcha.score_threshold', 0.5);
    }

    /**
     * Validate reCAPTCHA token
     *
     * @param string $token
     * @param string $action
     * @return array
     */
    public function validateToken(string $token, string $action = 'shorten_url')
    {
        if (!$this->secretKey) {
            Log::warning('reCAPTCHA secret key not configured');
            return [
                'success' => false,
                'error' => 'reCAPTCHA not configured',
                'score' => 0,
            ];
        }

        try {
            $response = Http::timeout(10)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $this->secretKey,
                'response' => $token,
            ]);

            $result = $response->json();

            if (!$result['success']) {
                Log::warning('reCAPTCHA validation failed', [
                    'errors' => $result['error-codes'] ?? ['unknown_error'],
                    'action' => $action,
                ]);

                return [
                    'success' => false,
                    'error' => 'reCAPTCHA validation failed',
                    'error_codes' => $result['error-codes'] ?? [],
                    'score' => 0,
                ];
            }

            // Check action
            if (!isset($result['action']) || $result['action'] !== $action) {
                Log::warning('reCAPTCHA action mismatch', [
                    'expected' => $action,
                    'received' => $result['action'] ?? 'unknown',
                ]);

                return [
                    'success' => false,
                    'error' => 'reCAPTCHA action mismatch',
                    'score' => $result['score'] ?? 0,
                ];
            }

            // Check score threshold
            $score = $result['score'] ?? 0;
            if ($score < $this->scoreThreshold) {
                Log::warning('reCAPTCHA score below threshold', [
                    'score' => $score,
                    'threshold' => $this->scoreThreshold,
                    'action' => $action,
                ]);

                return [
                    'success' => false,
                    'error' => 'reCAPTCHA score too low',
                    'score' => $score,
                ];
            }

            return [
                'success' => true,
                'score' => $score,
                'action' => $result['action'],
                'challenge_ts' => $result['challenge_ts'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('reCAPTCHA API error', [
                'error' => $e->getMessage(),
                'action' => $action,
            ]);

            return [
                'success' => false,
                'error' => 'reCAPTCHA service unavailable',
                'score' => 0,
            ];
        }
    }

    /**
     * Check if reCAPTCHA is configured and enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return !empty($this->secretKey);
    }
}
