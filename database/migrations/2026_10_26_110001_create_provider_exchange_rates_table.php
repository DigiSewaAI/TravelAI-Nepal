<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provider_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('currency_code', 10);
            $table->string('currency_name', 100)->nullable();
            $table->string('flag_emoji', 16)->nullable();
            $table->decimal('buy_rate', 10, 4)->default(0);
            $table->decimal('sell_rate', 10, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('effective_date');
            $table->timestamps();

            $table->unique(['provider_id', 'currency_code', 'effective_date'], 'per_provider_cur_date_unique');
            $table->index(['provider_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_exchange_rates');
    }
};