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
        Schema::table('links', function (Blueprint $table) {
            $table->boolean('is_password_protected')->default(false)->after('is_disabled');
            $table->string('password_hash')->nullable()->after('is_password_protected');
            $table->string('password_salt')->nullable()->after('password_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn(['is_password_protected', 'password_hash', 'password_salt']);
        });
    }
};
