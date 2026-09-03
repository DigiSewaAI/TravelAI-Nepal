<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itinerary_items', function (Blueprint $table) {
            // यदि ENUM छ भने VARCHAR मा बदल्नुहोस्
            $table->string('pricing_source', 50)->change();
            // वा
            // $table->string('pricing_source', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('itinerary_items', function (Blueprint $table) {
            $table->string('pricing_source', 20)->change();
        });
    }
};