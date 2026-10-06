<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bookings: rate snapshot at booking time (immutable)
        if (!Schema::hasColumn('bookings', 'exchange_rate_snapshot')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->decimal('exchange_rate_snapshot', 15, 6)
                    ->nullable()
                    ->after('guest_count');
                $table->string('rate_base_currency', 3)
                    ->nullable()
                    ->after('exchange_rate_snapshot');
            });
        }

        // Invoices: rate at invoice creation (immutable)
        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'exchange_rate_at_creation')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->decimal('exchange_rate_at_creation', 15, 6)
                    ->nullable()
                    ->after('total');
                $table->string('rate_base_currency', 3)
                    ->nullable()
                    ->after('exchange_rate_at_creation');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bookings', 'exchange_rate_snapshot')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn(['exchange_rate_snapshot', 'rate_base_currency']);
            });
        }

        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'exchange_rate_at_creation')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn(['exchange_rate_at_creation', 'rate_base_currency']);
            });
        }
    }
};