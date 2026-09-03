<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waypoints', function (Blueprint $table) {
            $table->foreignId('location_id')
                  ->nullable()
                  ->after('altitude')
                  ->constrained('locations')
                  ->nullOnDelete();
            
            $table->index('location_id');
        });
    }

    public function down(): void
    {
        Schema::table('waypoints', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });
    }
};