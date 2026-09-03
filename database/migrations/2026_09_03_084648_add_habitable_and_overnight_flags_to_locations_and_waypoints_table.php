<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->boolean('is_habitable')->default(true)
                ->after('longitude')
                ->comment('Whether accommodation/services exist at this location');
        });

        Schema::table('waypoints', function (Blueprint $table) {
            $table->boolean('is_overnight_stop')->default(true)
                ->after('altitude')
                ->comment('Whether trekkers stay overnight at this waypoint');
        });
    }

    public function down()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('is_habitable');
        });
        Schema::table('waypoints', function (Blueprint $table) {
            $table->dropColumn('is_overnight_stop');
        });
    }
};