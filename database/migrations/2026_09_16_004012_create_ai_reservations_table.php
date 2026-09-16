<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_reservations')) {
            return;
        }

        Schema::create('ai_reservations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('provider_id')
                ->nullable()
                ->constrained('providers')
                ->cascadeOnDelete();

            $table->string('guest_ip_hash', 64)->nullable();

            // YYYY-MM for provider, YYYY-MM-DD for guest
            $table->string('period', 10);

            $table->enum('status', ['reserved', 'completed', 'released', 'expired'])
                ->default('reserved');

            $table->timestamp('reserved_at')->useCurrent();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('expires_at');

            $table->string('idempotency_key', 64);
            $table->string('endpoint', 255)->nullable();

            $table->timestamps();

            $table->index(['provider_id', 'period', 'status'], 'ai_res_provider_period_status');
            $table->index(['guest_ip_hash', 'period', 'status'], 'ai_res_guest_period_status');
            $table->index(['status', 'expires_at'], 'ai_res_status_expires');
            $table->unique('idempotency_key', 'ai_res_idempotency_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_reservations');
    }
};