<?php
/**
 * Users Table Creation Script
 *
 * This script creates the complete users table with all necessary columns
 * for authentication, password encryption, and social login (Google/Facebook).
 *
 * Run this script when you need to create the users table manually.
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Checking users table...\n";

if (Schema::hasTable('users')) {
    echo "⚠️  Users table already exists.\n";
    echo "Checking columns...\n";

    $columns = Schema::getColumnListing('users');
    $requiredColumns = [
        'id', 'name', 'email', 'email_verified_at', 'password',
        'password_salt', 'google_id', 'facebook_id', 'facebook_token',
        'remember_token', 'created_at', 'updated_at'
    ];

    $missingColumns = array_diff($requiredColumns, $columns);

    if (empty($missingColumns)) {
        echo "✅ Users table has all required columns!\n";
    } else {
        echo "❌ Users table is missing columns: " . implode(', ', $missingColumns) . "\n";
        echo "Please drop the table and run this script again, or manually add the missing columns.\n";
    }
} else {
    echo "Creating users table...\n";

    Schema::create('users', function (Blueprint $table) {
        $table->id();

        // Basic user information
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();

        // Password authentication
        $table->string('password');
        $table->string('password_salt', 64)->nullable();

        // Social login fields
        $table->string('google_id')->nullable()->unique();
        $table->string('facebook_id')->nullable()->unique();
        $table->text('facebook_token')->nullable();

        // Laravel authentication
        $table->rememberToken();
        $table->timestamps();
    });

    echo "✅ Users table created successfully with all required columns!\n";
}
echo "\nTable includes:\n";
echo "- Basic auth: name, email, password, remember_token\n";
echo "- Security: password_salt for enhanced encryption\n";
echo "- Social login: google_id, facebook_id, facebook_token\n";
echo "- Timestamps: created_at, updated_at\n";
