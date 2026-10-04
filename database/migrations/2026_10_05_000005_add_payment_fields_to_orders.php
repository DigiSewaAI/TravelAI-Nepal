<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('payment_methods_snapshot')->nullable()->after('payment_method');
            $table->string('payment_reference', 100)->nullable()->after('paid_at');
            $table->text('payment_note')->nullable()->after('payment_reference');
            $table->timestamp('payment_notice_sent_at')->nullable()->after('payment_note');
            $table->timestamp('payment_verified_at')->nullable()->after('payment_notice_sent_at');
            $table->foreignId('payment_verified_by')->nullable()->after('payment_verified_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['payment_verified_by']);
            $table->dropColumn([
                'payment_methods_snapshot', 'payment_reference', 'payment_note',
                'payment_notice_sent_at', 'payment_verified_at', 'payment_verified_by',
            ]);
        });
    }
};