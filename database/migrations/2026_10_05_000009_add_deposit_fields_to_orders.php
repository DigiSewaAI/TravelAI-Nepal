<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('deposit_total', 12, 2)->default(0)->after('subtotal');
            $table->decimal('deposit_refunded_amount', 12, 2)->default(0)->after('deposit_total');
            $table->timestamp('deposit_refunded_at')->nullable()->after('deposit_refunded_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'deposit_total',
                'deposit_refunded_amount',
                'deposit_refunded_at',
            ]);
        });
    }
};