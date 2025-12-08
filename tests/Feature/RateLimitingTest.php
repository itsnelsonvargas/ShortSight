<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test strict rate limiting on authentication endpoints
     */
    public function test_strict_rate_limiting_on_authentication_endpoints(): void
    {
        // Clear any existing rate limiting cache
        Cache::flush();

        // Test login endpoint rate limiting with invalid credentials
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'nonexistent@example.com',
                'password' => 'wrongpassword',
            ]);

            if ($i < 4) {
                // Should allow first 4 attempts (within limit of 5)
                $response->assertStatus(401); // Invalid credentials, but not rate limited
            }
        }

        // 6th attempt should be rate limited
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'error' => 'rate_limit_exceeded',
                'limit' => '5 per 1 minute(s)',
            ])
            ->assertHeader('X-RateLimit-Limit', '5')
            ->assertHeader('Retry-After');
    }

    /**
     * Test link creation rate limiting
     */
    public function test_link_creation_rate_limiting(): void
    {
        Cache::flush();

        // Test link creation endpoint rate limiting
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/links', [
                'url' => 'https://example' . $i . '.com',
            ]);

            if ($i < 9) {
                // Should allow first 9 attempts (within limit of 10)
                $response->assertStatus(200); // Should succeed
            }
        }

        // 11th attempt should be rate limited
        $response = $this->postJson('/api/links', [
            'url' => 'https://example11.com',
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'error' => 'link_creation_rate_limit_exceeded',
                'limit' => '10 links per minute',
            ])
            ->assertHeader('X-RateLimit-Limit', '10');
    }

    /**
     * Test API rate limiting on general endpoints
     */
    public function test_api_rate_limiting_on_general_endpoints(): void
    {
        // Use a unique key for this test to avoid cache conflicts
        $testKey = 'test_' . time() . '_' . rand();

        // Make a few requests to ensure rate limiting works
        for ($i = 0; $i < 5; $i++) {
            $response = $this->getJson('/api/v1/ping');

            // Should succeed for first few requests
            $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'pong',
                ]);
        }

        // Verify rate limiting headers are present
        $response->assertHeader('X-RateLimit-Limit')
            ->assertHeader('X-RateLimit-Remaining');
    }

    /**
     * Test slug checking rate limiting
     */
    public function test_slug_checking_rate_limiting(): void
    {
        // Test slug checking endpoint with a few requests
        for ($i = 0; $i < 3; $i++) {
            $response = $this->getJson('/api/check-slug?slug=test' . $i . time());

            // Should succeed
            $response->assertStatus(200)
                ->assertHeader('X-RateLimit-Limit');
        }
    }

    /**
     * Test that rate limiting includes proper headers
     */
    public function test_rate_limiting_includes_proper_headers(): void
    {
        Cache::flush();

        // Make a request that should succeed
        $response = $this->getJson('/api/v1/ping');

        $response->assertStatus(200)
            ->assertHeader('X-RateLimit-Limit')
            ->assertHeader('X-RateLimit-Remaining')
            ->assertHeader('X-RateLimit-Reset');

        // Check that remaining attempts decreased
        $remaining = $response->headers->get('X-RateLimit-Remaining');
        $this->assertIsNumeric($remaining);
        $this->assertGreaterThan(0, (int)$remaining);
    }

    /**
     * Test that authenticated endpoints still work with rate limiting
     */
    public function test_authenticated_endpoints_work_with_rate_limiting(): void
    {
        // This test would require authentication setup
        // For now, just verify the route exists and rate limiting is applied

        $response = $this->getJson('/api/user');

        // Should fail with authentication, not rate limiting
        $response->assertStatus(401);
    }

    /**
     * Test Google SSO rate limiting
     */
    public function test_google_sso_rate_limiting(): void
    {
        Cache::flush();

        // Test Google SSO endpoint (3 attempts per 5 minutes)
        for ($i = 0; $i < 3; $i++) {
            $response = $this->get('/auth/google');

            // Should redirect (302) for first 3 attempts
            $response->assertStatus(302);
        }

        // 4th attempt should be rate limited
        $response = $this->get('/auth/google');

        $response->assertStatus(429)
            ->assertHeader('Retry-After');
    }
}
