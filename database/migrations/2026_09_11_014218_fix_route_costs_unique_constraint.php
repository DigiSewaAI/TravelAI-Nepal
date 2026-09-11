<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('route_costs', function (Blueprint $table) {
            $table->dropUnique(['route_id', 'type', 'effective_from']);
        });

        Schema::table('route_costs', function (Blueprint $table) {
            $table->unique(
                ['route_id', 'type', 'name', 'effective_from'],
                'route_costs_route_type_name_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('route_costs', function (Blueprint $table) {
            $table->dropUnique('route_costs_route_type_name_date_unique');
        });

        Schema::table('route_costs', function (Blueprint $table) {
            $table->unique(['route_id', 'type', 'effective_from']);
        });
    }
};