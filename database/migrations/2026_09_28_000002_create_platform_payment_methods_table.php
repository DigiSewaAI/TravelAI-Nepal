<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['bank', 'esewa', 'khalti', 'other']);
            $table->string('label');                    // "NIC Asia Bank", "eSewa QR"
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('identifier')->nullable();   // eSewa ID / phone
            $table->string('bank_name')->nullable();
            $table->string('qr_image_path')->nullable();
            $table->char('currency', 3)->default('NPR');
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_payment_methods');
    }
};