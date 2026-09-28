<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'bank', 'esewa', 'khalti',
                'paypal', 'wise', 'cash', 'international_bank',
            ]);
            $table->string('label')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('identifier')->nullable();   // email / phone / ID
            $table->string('bank_name')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('qr_image_path')->nullable();
            $table->char('currency', 3)->default('NPR');
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['provider_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_payment_methods');
    }
};