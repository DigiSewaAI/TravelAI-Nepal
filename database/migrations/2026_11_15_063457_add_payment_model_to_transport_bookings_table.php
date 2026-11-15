<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_bookings', 'payment_model')) {
                $table->enum('payment_model', ['advance', 'pay_on_arrival'])
                    ->default('pay_on_arrival')->after('payment_status');
            }
            if (!Schema::hasColumn('transport_bookings', 'payment_submitted_at')) {
                $table->timestamp('payment_submitted_at')->nullable()->after('payment_proof');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('transport_bookings', 'payment_model')) {
                $table->dropColumn('payment_model');
            }
            if (Schema::hasColumn('transport_bookings', 'payment_submitted_at')) {
                $table->dropColumn('payment_submitted_at');
            }
        });
    }
};