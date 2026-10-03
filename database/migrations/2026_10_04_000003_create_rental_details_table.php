<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->onDelete('cascade');
            $table->decimal('rental_price_per_day', 10, 2);
            $table->decimal('rental_deposit', 10, 2)->nullable();
            $table->string('rental_condition', 50)->nullable();
            $table->integer('rental_min_days')->nullable();
            $table->integer('rental_max_days')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_details');
    }
};