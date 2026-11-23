<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ready-made enquiries don't require a deadline.
        // Relaxing NOT NULL → NULL is non-destructive (existing dates preserved).
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->date('deadline')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            // Restore NOT NULL only if no NULL rows exist (safety guard)
            $nullCount = \DB::table('custom_orders')->whereNull('deadline')->count();
            if ($nullCount === 0) {
                $table->date('deadline')->nullable(false)->change();
            }
        });
    }
};