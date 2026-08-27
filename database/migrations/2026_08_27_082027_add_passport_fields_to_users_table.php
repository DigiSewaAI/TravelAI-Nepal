<?php
// database/migrations/2026_08_28_000002_add_passport_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ✅ Public ID (UUID) - UNIQUE ले नै index प्रदान गर्छ, त्यसैले छुट्टै index आवश्यक छैन
            $table->char('passport_public_id', 36)->nullable()->unique();
            
            // ✅ Privacy setting (Default: Private)
            $table->enum('passport_privacy', ['private', 'unlisted', 'public'])
                  ->default('private');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['passport_public_id', 'passport_privacy']);
        });
    }
};