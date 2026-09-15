<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscriptions') || !Schema::hasColumn('subscriptions', 'end_date')) {
            return;
        }

        // Pre-flight: log existing values before change
        $before = DB::table('subscriptions')
            ->select('id', 'end_date')
            ->orderBy('id')
            ->get();
        foreach ($before as $row) {
            \Log::info('FIX-04 migration: before', [
                'id' => $row->id,
                'end_date' => $row->end_date,
            ]);
        }

        // Change DATE → DATETIME (existing dates become midnight)
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dateTime('end_date')->nullable()->change();
        });

        // Post-flight: log after change
        $after = DB::table('subscriptions')
            ->select('id', 'end_date')
            ->orderBy('id')
            ->get();
        foreach ($after as $row) {
            \Log::info('FIX-04 migration: after', [
                'id' => $row->id,
                'end_date' => $row->end_date,
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('subscriptions') || !Schema::hasColumn('subscriptions', 'end_date')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->date('end_date')->nullable()->change();
        });
    }
};