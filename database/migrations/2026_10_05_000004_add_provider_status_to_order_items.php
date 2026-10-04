<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->enum('provider_status', ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'])
                ->default('pending')
                ->after('line_total');
            $table->timestamp('provider_status_updated_at')->nullable()->after('provider_status');
            $table->index(['provider_id', 'provider_status']);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['provider_id', 'provider_status']);
            $table->dropColumn(['provider_status', 'provider_status_updated_at']);
        });
    }
};