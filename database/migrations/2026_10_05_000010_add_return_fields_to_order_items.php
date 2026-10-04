<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->timestamp('return_requested_at')->nullable()->after('provider_status_updated_at');
            $table->timestamp('return_confirmed_at')->nullable()->after('return_requested_at');
            $table->enum('return_condition', ['good', 'damaged', 'lost'])->nullable()->after('return_confirmed_at');
            $table->text('return_notes')->nullable()->after('return_condition');
            $table->decimal('deposit_refund_amount', 10, 2)->nullable()->after('return_notes');
            $table->timestamp('deposit_refunded_at')->nullable()->after('deposit_refund_amount');

            $table->index(['order_id', 'return_requested_at']);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'return_requested_at']);
            $table->dropColumn([
                'return_requested_at', 'return_confirmed_at', 'return_condition',
                'return_notes', 'deposit_refund_amount', 'deposit_refunded_at',
            ]);
        });
    }
};