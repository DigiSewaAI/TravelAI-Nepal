<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->enum('itinerary_status', ['draft', 'published'])
                ->default('draft')
                ->after('status');

            $table->index('itinerary_status');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['itinerary_status']);
            $table->dropColumn('itinerary_status');
        });
    }
};