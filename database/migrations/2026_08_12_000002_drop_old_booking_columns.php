<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['trekker_id']);
            $table->dropForeign(['trek_id']);

            // Drop columns
            $table->dropColumn(['trekker_id', 'trek_id']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('trekker_id')->constrained('trekkers')->onDelete('cascade');
            $table->foreignId('trek_id')->constrained('treks')->onDelete('cascade');
        });
    }
};