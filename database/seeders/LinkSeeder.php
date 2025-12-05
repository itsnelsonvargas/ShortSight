<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Link;

class LinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the test user
        $testUser = User::where('email', 'test@example.com')->first();

        if (!$testUser) {
            $this->command->error('Test user not found. Please run the app:init command instead.');
            return;
        }

        $links = [
            [
                'user' => $testUser->id,
                'title' => 'Facebook Social Media',
                'description' => 'Connect with friends and family on the world\'s largest social network',
                'url' => 'https://facebook.com',
                'slug' => 'facebook',
                'is_disabled' => false,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'user' => $testUser->id,
                'title' => 'YouTube Video Platform',
                'description' => 'Watch and share videos from creators around the world',
                'url' => 'https://youtube.com',
                'slug' => 'youtube',
                'is_disabled' => false,
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ],
            [
                'user' => $testUser->id,
                'title' => 'Product Launch Campaign',
                'description' => 'Check out our amazing new product features and capabilities',
                'url' => 'https://example.com/my-awesome-product-launch-2024',
                'slug' => 'product',
                'is_disabled' => false,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'user' => $testUser->id,
                'title' => 'Business Growth Blog',
                'description' => 'Learn how to grow your business with proven strategies',
                'url' => 'https://blog.example.com/how-to-grow-your-business',
                'slug' => 'blog',
                'is_disabled' => false,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user' => $testUser->id,
                'title' => 'Black Friday Deals',
                'description' => 'Limited time offers - up to 70% off on all products',
                'url' => 'https://example.com/black-friday-deals',
                'slug' => 'promo',
                'is_disabled' => true,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'user' => $testUser->id,
                'title' => 'Product Demo Video',
                'description' => 'See our product in action with this comprehensive demo',
                'url' => 'https://example.com/product-demo-video',
                'slug' => 'demo',
                'is_disabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user' => $testUser->id,
                'title' => 'Instagram Profile',
                'description' => 'Follow us on Instagram for daily updates and behind-the-scenes content',
                'url' => 'https://instagram.com/mycompany',
                'slug' => 'social',
                'is_disabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($links as $linkData) {
            Link::create($linkData);
        }

        $this->command->info('Created ' . count($links) . ' sample links for test user.');
    }
}
