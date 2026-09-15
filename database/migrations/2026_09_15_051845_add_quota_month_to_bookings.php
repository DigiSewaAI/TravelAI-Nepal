<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'quota_month')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('quota_month', 7)->nullable()->after('status');
                $table->index('quota_month');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bookings', 'quota_month')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropIndex(['quota_month']);
                $table->dropColumn('quota_month');
            });
        }
    }
};