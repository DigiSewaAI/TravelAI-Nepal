<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('provider_id')->constrained()->onDelete('restrict');
            $table->string('product_name');
            $table->enum('product_type', ['shop', 'rental', 'wholesale']);
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity');
            $table->date('rental_start_date')->nullable();
            $table->date('rental_end_date')->nullable();
            $table->integer('rental_days')->nullable();
            $table->decimal('rental_deposit', 10, 2)->nullable();
            $table->integer('bulk_min_qty')->nullable();
            $table->decimal('line_total', 12, 2);
            $table->timestamps();

            $table->index('order_id');
            $table->index(['provider_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};