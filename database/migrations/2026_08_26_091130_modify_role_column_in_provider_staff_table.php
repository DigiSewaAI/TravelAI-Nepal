<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_staff', function (Blueprint $table) {
            // `role` column लाई VARCHAR(255) मा बदल्नुहोस्
            $table->string('role', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('provider_staff', function (Blueprint $table) {
            // Rollback को लागि पुरानो length मा फर्काउने (यदि आवश्यक छ भने)
            $table->string('role', 20)->nullable()->change();
        });
    }
};