<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exchange_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 32)->unique();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('customer_name', 150);
            $table->string('customer_initials', 4)->nullable();
            $table->string('type', 10); // buy | sell
            $table->string('currency_code', 10);
            $table->decimal('amount_foreign', 12, 2)->default(0);
            $table->decimal('rate', 10, 4)->default(0);
            $table->decimal('npr_amount', 14, 2)->default(0);
            $table->decimal('profit', 10, 2)->default(0);
            $table->string('status', 20)->default('completed'); // completed|pending|cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['provider_id', 'created_at']);
            $table->index(['provider_id', 'type']);
            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_transactions');
    }
};