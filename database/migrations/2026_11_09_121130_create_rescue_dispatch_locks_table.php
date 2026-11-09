<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rescue_dispatch_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sos_alert_id')->constrained('sos_alerts')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rescue_dispatch_locks');
    }
};