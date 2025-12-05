<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AppInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:init {--skip-db : Skip database drop/create operations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize the application: drop/create database, migrate, seed, and create test user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $skipDb = $this->option('skip-db');

        if (!$skipDb) {
            // Drop and create database
            $this->dropAndCreateDatabase();
        }

        // Run migrations
        $this->info('Running migrations...');
        $exitCode = Artisan::call('migrate:fresh', ['--force' => true]);
        if ($exitCode !== 0) {
            $this->error('Migration failed!');
            return $exitCode;
        }

        // Create test user first (needed by seeders)
        $this->createTestUser();

        // Run seeders
        $this->info('Running database seeders...');
        $exitCode = Artisan::call('db:seed', ['--force' => true]);
        if ($exitCode !== 0) {
            $this->error('Seeding failed!');
            return $exitCode;
        }

        $this->info('🎉 App initialized successfully!');
        $this->line('');
        $this->info('Test user credentials:');
        $this->line('Email: test@example.com');
        $this->line('Password: password');
        $this->line('');
        $this->info('You can now start the development server with: php artisan serve');
    }

    /**
     * Drop and create the database.
     */
    private function dropAndCreateDatabase()
    {
        $databaseName = config('database.connections.sqlite.database');
        $databasePath = database_path('database.sqlite');

        $this->info('Setting up database...');

        // For SQLite, just delete and recreate the file
        if (file_exists($databasePath)) {
            $this->info('Removing existing database file...');
            unlink($databasePath);
        }

        // Create the database file
        $this->info('Creating new database file...');
        touch($databasePath);

        $this->info('Database setup complete.');
    }

    /**
     * Create a test user for development.
     */
    private function createTestUser()
    {
        $this->info('Creating test user...');

        // Check if test user already exists
        $existingUser = User::where('email', 'test@example.com')->first();

        if ($existingUser) {
            $this->info('Test user already exists, skipping creation.');
            return;
        }

        // Create test user
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->info('Test user created successfully.');
    }
}
