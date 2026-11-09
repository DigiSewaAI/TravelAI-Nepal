<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rescue_missions', function (Blueprint $table) {
            // यसले एउटै SOS को लागि एउटा मात्र मिसन बन्न दिन्छ
            $table->unique('sos_alert_id');
        });
    }

    public function down(): void
    {
        Schema::table('rescue_missions', function (Blueprint $table) {
            $table->dropUnique(['sos_alert_id']);
        });
    }
};