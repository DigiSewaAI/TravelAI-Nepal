<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_details', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_details', 'pickup_point')) {
                $table->string('pickup_point', 255)->nullable()->after('to_location_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transport_details', function (Blueprint $table) {
            if (Schema::hasColumn('transport_details', 'pickup_point')) {
                $table->dropColumn('pickup_point');
            }
        });
    }
};