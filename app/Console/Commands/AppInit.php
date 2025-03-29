<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AppInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
    
        // Run migrations
        $this->info('Running migrations...');
        Artisan::call('migrate', ['--force' => true]);  // --force to bypass confirmation in production

        // Run seeders
        $this->info('Running database seeders...');
        Artisan::call('db:seed', ['--force' => true]);

        // Any other initialization code here (optional)
        $this->info('App initialized successfully!');
    }
}
