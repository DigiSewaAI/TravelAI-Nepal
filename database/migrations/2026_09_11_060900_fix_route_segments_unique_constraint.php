<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Step 1: Drop buggy unique constraint (NULL semantics broken) ───
        Schema::table('route_segments', function (Blueprint $table) {
            $table->dropUnique(['route_id', 'sequence', 'deleted_at']);
        });

        // ─── Step 2: Add functional unique index (MySQL 8.0.13+) ───
        // COALESCE(deleted_at, '1970-01-01') treats NULL as sentinel date
        // so multiple active rows with same (route_id, sequence) are rejected
        DB::statement("
            CREATE UNIQUE INDEX route_segments_active_unique
            ON route_segments (
                route_id,
                sequence,
                (COALESCE(deleted_at, '1970-01-01 00:00:00'))
            )
        ");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX route_segments_active_unique ON route_segments");

        Schema::table('route_segments', function (Blueprint $table) {
            $table->unique(['route_id', 'sequence', 'deleted_at']);
        });
    }
};