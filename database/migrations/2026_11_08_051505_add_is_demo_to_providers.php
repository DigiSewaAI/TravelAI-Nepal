<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('providers', 'is_demo')) {
            Schema::table('providers', function (Blueprint $table) {
                $table->boolean('is_demo')->default(false)->after('is_active');
                $table->index('is_demo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('providers', 'is_demo')) {
            Schema::table('providers', function (Blueprint $table) {
                $table->dropColumn('is_demo');
            });
        }
    }
};