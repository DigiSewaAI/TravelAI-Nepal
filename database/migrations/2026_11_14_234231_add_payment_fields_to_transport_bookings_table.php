<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_bookings', 'payment_status')) {
                $table->string('payment_status', 20)->default('pending')->after('status');
            }
            if (!Schema::hasColumn('transport_bookings', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('transport_bookings', 'payment_ref')) {
                $table->string('payment_ref', 100)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('transport_bookings', 'payment_proof')) {
                $table->string('payment_proof')->nullable()->after('payment_ref');
            }
            if (!Schema::hasColumn('transport_bookings', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_proof');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            foreach (['payment_status', 'payment_method', 'payment_ref', 'payment_proof', 'paid_at'] as $col) {
                if (Schema::hasColumn('transport_bookings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};