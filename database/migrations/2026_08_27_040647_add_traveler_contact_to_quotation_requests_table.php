<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->string('traveler_name')->nullable()->after('traveler_id');
            $table->string('traveler_email')->nullable()->after('traveler_name');
            $table->string('traveler_phone')->nullable()->after('traveler_email');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->dropColumn(['traveler_name', 'traveler_email', 'traveler_phone']);
        });
    }
};