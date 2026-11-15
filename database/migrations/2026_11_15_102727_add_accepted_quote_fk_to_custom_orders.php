<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('custom_orders', 'accepted_quote_id')) {
            $hasFk = collect(\DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'custom_orders'
                  AND COLUMN_NAME = 'accepted_quote_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            "))->isNotEmpty();

            if (!$hasFk) {
                Schema::table('custom_orders', function (Blueprint $table) {
                    $table->foreign('accepted_quote_id')
                        ->references('id')->on('custom_order_quotes')
                        ->onDelete('set null');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropForeign(['accepted_quote_id']);
        });
    }
};