<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_details', function (Blueprint $table) {
            $table->enum('property_type', ['hotel', 'resort', 'lodge', 'homestay'])
                  ->default('hotel')
                  ->after('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('hotel_details', function (Blueprint $table) {
            $table->dropColumn('property_type');
        });
    }
};