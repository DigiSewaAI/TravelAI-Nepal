<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('individual_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('individual_bookings', 'service_id')) {
                $table->foreignId('service_id')->nullable()->after('provider_id')->constrained('services')->nullOnDelete();
            }
            if (!Schema::hasColumn('individual_bookings', 'customer_phone')) {
                $table->string('customer_phone', 30)->nullable()->after('customer_email');
            }
            if (!Schema::hasColumn('individual_bookings', 'notes')) {
                $table->text('notes')->nullable()->after('duration');
            }
            if (!Schema::hasColumn('individual_bookings', 'price_per_day_snapshot')) {
                $table->decimal('price_per_day_snapshot', 10, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('individual_bookings', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('individual_bookings', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('confirmed_at');
            }
            if (!Schema::hasColumn('individual_bookings', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('individual_bookings', 'payment_status')) {
                $table->string('payment_status', 20)->default('pending')->after('cancelled_at');
            }
            if (!Schema::hasColumn('individual_bookings', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('individual_bookings', 'payment_ref')) {
                $table->string('payment_ref', 100)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('individual_bookings', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_ref');
            }
        });
    }

    public function down(): void
    {
        Schema::table('individual_bookings', function (Blueprint $table) {
            $cols = ['service_id','customer_phone','notes','price_per_day_snapshot','confirmed_at','completed_at','cancelled_at','payment_status','payment_method','payment_ref','paid_at'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('individual_bookings', $c)) {
                    if ($c === 'service_id') {
                        $table->dropForeign(['service_id']);
                    }
                    $table->dropColumn($c);
                }
            }
        });
    }
};