<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_guest_usage')) {
            return;
        }

        Schema::create('ai_guest_usage', function (Blueprint $table) {
            $table->id();

            $table->string('guest_ip_hash', 64);

            $table->integer('count')->default(0);

            // YYYY-MM-DD (daily quota for guests)
            $table->string('period', 10);

            $table->timestamps();

            $table->unique(['guest_ip_hash', 'period'], 'ai_guest_usage_unique');
            $table->index(['guest_ip_hash', 'period'], 'ai_guest_usage_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_guest_usage');
    }
};