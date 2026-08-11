<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign keys first
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['trekker_id']);
            $table->dropColumn('trekker_id');
        });

        Schema::table('sos_alerts', function (Blueprint $table) {
            $table->dropForeign(['trekker_id']);
            $table->dropColumn('trekker_id');
        });

        Schema::table('treks', function (Blueprint $table) {
            $table->dropForeign(['agency_id']);
            $table->dropForeign(['service_id']);
        });

        // Now drop tables
        Schema::dropIfExists('trekkers');
        Schema::dropIfExists('treks');
        Schema::dropIfExists('agencies');
    }

    public function down(): void
    {
        // Cannot restore dropped tables easily, but we can define schema
        // This is a destructive migration; rolling back is not recommended.
    }
};