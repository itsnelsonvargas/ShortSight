<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Link::insert([
            [
            'url' => 'https://facebook.com',
            'short' => 'fb',
            'created_at' => now(),
            'updated_at' => now(),
            ],
            [
            'url' => 'https://youtube.com',
            'short' => 'yt',
            'created_at' => now(),
            'updated_at' => now(),
            ],
        ]);
    }
}
