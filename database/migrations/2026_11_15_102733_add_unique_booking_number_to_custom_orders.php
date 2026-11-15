<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Backfill NULL booking numbers safely (idempotent — re-runs skip if none)
        $year = date('Y');
        $prefix = 'ART-' . $year . '-';

        $nullOrders = DB::table('custom_orders')->whereNull('booking_number')->orderBy('id')->get();
        foreach ($nullOrders as $order) {
            $maxSeq = (int) (DB::table('custom_orders')
                ->where('booking_number', 'like', $prefix . '%')
                ->selectRaw('MAX(CAST(SUBSTRING(booking_number, -4) AS UNSIGNED)) as max_seq')
                ->value('max_seq') ?? 0);

            $candidate = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);

            DB::table('custom_orders')
                ->where('id', $order->id)
                ->update(['booking_number' => $candidate]);
        }

        // Step 2: Add unique index only if not already present
        $indexExists = collect(DB::select("
            SHOW INDEX FROM custom_orders WHERE Key_name = 'custom_orders_booking_number_unique'
        "))->isNotEmpty();

        if (!$indexExists) {
            Schema::table('custom_orders', function (Blueprint $table) {
                $table->unique('booking_number', 'custom_orders_booking_number_unique');
            });
        }
    }

    public function down(): void
    {
        $indexExists = collect(DB::select("
            SHOW INDEX FROM custom_orders WHERE Key_name = 'custom_orders_booking_number_unique'
        "))->isNotEmpty();

        if ($indexExists) {
            Schema::table('custom_orders', function (Blueprint $table) {
                $table->dropUnique('custom_orders_booking_number_unique');
            });
        }
    }
};