<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            // Claim fields (all nullable — existing 25 orders untouched)
            $table->string('customer_email', 190)->nullable()->after('customer_phone');
            $table->string('receipt_code_hash', 64)->nullable()->after('customer_email');
            $table->timestamp('receipt_code_issued_at')->nullable()->after('receipt_code_hash');
            $table->timestamp('receipt_code_used_at')->nullable()->after('receipt_code_issued_at');
            $table->unsignedTinyInteger('receipt_code_reissue_count')->default(0)->after('receipt_code_used_at');

            // Index for claimable lookup
            $table->index(['receipt_code_used_at', 'created_at'], 'idx_claimable');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->dropIndex('idx_claimable');
            $table->dropColumn([
                'customer_email',
                'receipt_code_hash',
                'receipt_code_issued_at',
                'receipt_code_used_at',
                'receipt_code_reissue_count',
            ]);
        });
    }
};