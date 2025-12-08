<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Link;
use App\Models\Visitor;
use App\Services\DataExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataExportServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test data export service creates proper GDPR-compliant structure
     */
    public function test_data_export_service_creates_gdpr_compliant_structure(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create test data
        $link = Link::create([
            'user' => $user->id,
            'title' => 'Test Link',
            'description' => 'Test description',
            'url' => 'https://example.com',
            'slug' => 'test-link',
        ]);

        Visitor::create([
            'slug' => 'test-link',
            'ip_address' => '192.168.1.100',
            'country' => 'United States',
            'browser' => 'Chrome',
        ]);

        // Test the service
        $service = app(DataExportService::class);
        $data = $service->exportUserData($user);

        // Assert structure
        $this->assertArrayHasKey('export_metadata', $data);
        $this->assertArrayHasKey('user_profile', $data);
        $this->assertArrayHasKey('links', $data);
        $this->assertArrayHasKey('analytics', $data);
        $this->assertArrayHasKey('data_summary', $data);

        // Assert GDPR compliance
        $this->assertTrue($data['export_metadata']['gdpr_compliant']);
        $this->assertEquals('1.0', $data['export_metadata']['data_portability_version']);
        $this->assertEquals('JSON', $data['export_metadata']['data_format']);

        // Assert user profile data (without sensitive info)
        $this->assertEquals($user->id, $data['user_profile']['id']);
        $this->assertEquals($user->name, $data['user_profile']['name']);
        $this->assertEquals($user->email, $data['user_profile']['email']);
        $this->assertArrayNotHasKey('password', $data['user_profile']);
        $this->assertArrayNotHasKey('password_salt', $data['user_profile']);

        // Assert links data
        $this->assertCount(1, $data['links']);
        $this->assertEquals('Test Link', $data['links'][0]['title']);
        $this->assertEquals('https://example.com', $data['links'][0]['original_url']);
        $this->assertEquals('test-link', $data['links'][0]['short_slug']);

        // Assert data summary
        $this->assertEquals(1, $data['data_summary']['total_links_created']);
        $this->assertEquals(1, $data['data_summary']['total_clicks_received']);
    }

    /**
     * Test IP address anonymization
     */
    public function test_ip_address_anonymization(): void
    {
        $service = app(DataExportService::class);

        // Test IPv4 anonymization
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('anonymizeIp');
        $method->setAccessible(true);

        $anonymizedIpv4 = $method->invoke($service, '192.168.1.123');
        $this->assertEquals('192.168.1.0', $anonymizedIpv4);

        // Test IPv6 anonymization
        $anonymizedIpv6 = $method->invoke($service, '2001:0db8:85a3:0000:0000:8a2e:0370:7334');
        $this->assertEquals('2001:0db8:85a3:0000:0000:0000:0000:0000', $anonymizedIpv6);

        // Test null input
        $anonymizedNull = $method->invoke($service, null);
        $this->assertNull($anonymizedNull);
    }

    /**
     * Test data export with no user data
     */
    public function test_data_export_with_empty_user_data(): void
    {
        $user = User::factory()->create();

        $service = app(DataExportService::class);
        $data = $service->exportUserData($user);

        // Should still have proper structure even with no data
        $this->assertArrayHasKey('export_metadata', $data);
        $this->assertArrayHasKey('user_profile', $data);
        $this->assertArrayHasKey('links', $data);
        $this->assertArrayHasKey('analytics', $data);
        $this->assertArrayHasKey('data_summary', $data);

        $this->assertEmpty($data['links']);
        $this->assertEquals(0, $data['data_summary']['total_links_created']);
        $this->assertEquals(0, $data['data_summary']['total_clicks_received']);
    }
}
