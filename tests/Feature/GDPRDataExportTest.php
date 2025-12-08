<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Link;
use App\Models\Visitor;
use Laravel\Sanctum\Sanctum;

class GDPRDataExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test GDPR data export endpoint returns proper data structure
     */
    public function test_gdpr_data_export_returns_proper_structure(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create some test links for the user
        $link1 = Link::create([
            'user' => $user->id,
            'title' => 'Test Link 1',
            'description' => 'Description for test link 1',
            'url' => 'https://example.com/1',
            'slug' => 'test-link-1',
            'is_disabled' => false,
        ]);

        $link2 = Link::create([
            'user' => $user->id,
            'title' => 'Test Link 2',
            'description' => 'Description for test link 2',
            'url' => 'https://example.com/2',
            'slug' => 'test-link-2',
            'is_disabled' => true,
        ]);

        // Create some visitor data
        Visitor::create([
            'slug' => 'test-link-1',
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'browser' => 'Chrome',
            'device' => 'Desktop',
            'platform' => 'Windows',
            'country' => 'United States',
            'city' => 'New York',
            'referer' => 'https://google.com',
        ]);

        Visitor::create([
            'slug' => 'test-link-1',
            'ip_address' => '192.168.1.101',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
            'browser' => 'Safari',
            'device' => 'Mobile',
            'platform' => 'iOS',
            'country' => 'United States',
            'city' => 'Los Angeles',
            'referer' => 'https://facebook.com',
        ]);

        // Authenticate the user
        Sanctum::actingAs($user);

        // Test the data export info endpoint
        $response = $this->getJson('/api/user/data-export');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data_summary' => [
                    'total_links_created',
                    'total_clicks_received',
                    'active_links',
                    'disabled_links',
                    'account_age_days',
                    'data_export_completeness',
                ],
                'export_metadata' => [
                    'export_date',
                    'user_id',
                    'gdpr_compliant',
                    'data_portability_version',
                    'data_format',
                ],
                'download_available',
                'gdpr_compliant',
            ]);

        // Verify the data summary is correct
        $response->assertJson([
            'data_summary' => [
                'total_links_created' => 2,
                'total_clicks_received' => 2,
                'active_links' => 1,
                'disabled_links' => 1,
            ],
            'gdpr_compliant' => true,
            'download_available' => true,
        ]);
    }

    /**
     * Test GDPR data export download endpoint returns JSON file
     */
    public function test_gdpr_data_export_download_returns_json_file(): void
    {
        // Create a test user
        $user = User::factory()->create();

        // Authenticate the user
        Sanctum::actingAs($user);

        // Test the data export download endpoint
        $response = $this->get('/api/user/data-export/download');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeader('Content-Disposition', 'attachment; filename="shortsight-data-export-' . $user->id . '-');

        // Verify the response contains valid JSON
        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('export_metadata', $data);
        $this->assertArrayHasKey('user_profile', $data);
        $this->assertArrayHasKey('links', $data);
        $this->assertArrayHasKey('analytics', $data);
        $this->assertArrayHasKey('data_summary', $data);
    }

    /**
     * Test GDPR data export requires authentication
     */
    public function test_gdpr_data_export_requires_authentication(): void
    {
        // Test without authentication
        $response = $this->getJson('/api/user/data-export');
        $response->assertStatus(401);

        $response = $this->get('/api/user/data-export/download');
        $response->assertStatus(401);
    }

    /**
     * Test IP address anonymization in data export
     */
    public function test_ip_address_anonymization_in_data_export(): void
    {
        // Create a test user and link
        $user = User::factory()->create();
        $link = Link::create([
            'user' => $user->id,
            'title' => 'Test Link',
            'url' => 'https://example.com',
            'slug' => 'test-link',
        ]);

        // Create visitor with IPv4 address
        Visitor::create([
            'slug' => 'test-link',
            'ip_address' => '192.168.1.123',
            'country' => 'United States',
        ]);

        // Create visitor with IPv6 address
        Visitor::create([
            'slug' => 'test-link',
            'ip_address' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            'country' => 'Germany',
        ]);

        // Authenticate and get data export
        Sanctum::actingAs($user);
        $response = $this->get('/api/user/data-export/download');
        $data = json_decode($response->getContent(), true);

        // Check that IP addresses are anonymized
        $clicks = $data['analytics']['analytics_by_link'][0]['detailed_clicks'];

        // IPv4 should be anonymized (last octet becomes 0)
        $ipv4Click = collect($clicks)->first(function ($click) {
            return str_contains($click['ip_address_anonymized'], '192.168.1.0');
        });
        $this->assertNotNull($ipv4Click);

        // IPv6 should be anonymized (last segments become 0000)
        $ipv6Click = collect($clicks)->first(function ($click) {
            return str_contains($click['ip_address_anonymized'], '2001:0db8:85a3:0000:0000:0000:0000:0000');
        });
        $this->assertNotNull($ipv6Click);
    }
}
