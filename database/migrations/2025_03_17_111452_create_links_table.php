<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user')->nullable(); // Changed to unsignedBigInteger for user ID
            $table->string('title')->nullable();
            $table->string('description')->nullable(); 
            $table->string('url');
            $table->string('slug')->unique();
            
            $table->timestamps();
            $table->softDeletes(); // Added softDeletes for deleted_at column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
