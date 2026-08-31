<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tables = ['waypoints', 'routes', 'treks', 'locations'];
        
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'safety_status')) {
                        $table->string('safety_status')->nullable();
                    }
                    if (!Schema::hasColumn($tableName, 'safety_updated_at')) {
                        $table->timestamp('safety_updated_at')->nullable();
                    }
                });
            }
        }
    }

    public function down()
    {
        $tables = ['waypoints', 'routes', 'treks', 'locations'];
        
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropColumn(['safety_status', 'safety_updated_at']);
                });
            }
        }
    }
};