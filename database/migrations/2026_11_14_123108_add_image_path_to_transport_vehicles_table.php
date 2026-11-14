<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_vehicles', 'image_path')) {
                $table->string('image_path')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transport_vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('transport_vehicles', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};