<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planner_requests', function (Blueprint $table) {
            $table->string('currency', 3)->default('NPR')->after('budget');
            $table->decimal('exchange_rate_snapshot', 12, 6)->nullable()->after('currency');
        });

        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->string('currency', 3)->default('NPR')->after('traveler_input');
            $table->decimal('exchange_rate_snapshot', 12, 6)->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('planner_requests', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate_snapshot']);
        });

        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate_snapshot']);
        });
    }
};