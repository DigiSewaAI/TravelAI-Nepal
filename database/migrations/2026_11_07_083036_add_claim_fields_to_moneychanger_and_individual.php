<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MoneyChanger — exchange_transactions
        if (!Schema::hasColumn('exchange_transactions', 'customer_email')) {
            Schema::table('exchange_transactions', function (Blueprint $table) {
                $table->string('customer_email', 190)->nullable()->after('customer_initials');
                $table->string('receipt_code_hash', 64)->nullable()->after('customer_email');
                $table->timestamp('receipt_code_issued_at')->nullable()->after('receipt_code_hash');
                $table->timestamp('receipt_code_used_at')->nullable()->after('receipt_code_issued_at');
            });
            echo "  ✅ Added claim fields to exchange_transactions\n";
        } else {
            echo "  ⏭ exchange_transactions already has customer_email\n";
        }

        // Individual — individual_bookings
        if (!Schema::hasColumn('individual_bookings', 'customer_email')) {
            Schema::table('individual_bookings', function (Blueprint $table) {
                $table->string('customer_email', 190)->nullable()->after('client_name');
                $table->string('receipt_code_hash', 64)->nullable()->after('customer_email');
                $table->timestamp('receipt_code_issued_at')->nullable()->after('receipt_code_hash');
                $table->timestamp('receipt_code_used_at')->nullable()->after('receipt_code_issued_at');
            });
            echo "  ✅ Added claim fields to individual_bookings\n";
        } else {
            echo "  ⏭ individual_bookings already has customer_email\n";
        }
    }

    public function down(): void
    {
        // Rollback — safe (only drop the columns we added)
        Schema::table('exchange_transactions', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'receipt_code_hash', 'receipt_code_issued_at', 'receipt_code_used_at']);
        });

        Schema::table('individual_bookings', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'receipt_code_hash', 'receipt_code_issued_at', 'receipt_code_used_at']);
        });
    }
};