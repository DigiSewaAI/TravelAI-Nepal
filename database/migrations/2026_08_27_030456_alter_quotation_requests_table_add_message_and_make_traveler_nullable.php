<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            // Make traveler_id nullable
            $table->foreignId('traveler_id')->nullable()->change();
            
            // Add message column
            $table->text('message')->nullable()->after('traveler_input');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->foreignId('traveler_id')->nullable(false)->change();
            $table->dropColumn('message');
        });
    }
};