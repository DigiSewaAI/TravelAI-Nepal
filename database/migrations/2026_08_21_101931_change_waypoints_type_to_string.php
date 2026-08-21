<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: ENUM लाई VARCHAR मा convert गर
        DB::statement('ALTER TABLE waypoints MODIFY type VARCHAR(50)');
    }

    public function down(): void
    {
        // Rollback: original ENUM मा फर्काऊ
        DB::statement("ALTER TABLE waypoints MODIFY type ENUM('village','checkpoint','landmark','pass','peak','trailhead')");
    }
};