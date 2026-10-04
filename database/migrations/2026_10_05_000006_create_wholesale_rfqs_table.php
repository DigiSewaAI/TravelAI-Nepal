<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wholesale_rfqs', function (Blueprint $table) {
            $table->id();
            $table->string('rfq_number', 30)->unique();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->integer('quantity');
            $table->text('message')->nullable();
            $table->string('buyer_company', 150)->nullable();
            $table->string('buyer_phone', 20)->nullable();
            $table->decimal('quoted_price', 10, 2)->nullable();
            $table->decimal('quoted_total', 12, 2)->nullable();
            $table->text('provider_response')->nullable();
            $table->date('valid_until')->nullable();
            $table->enum('status', ['pending', 'quoted', 'accepted', 'rejected', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->index(['provider_id', 'status']);
            $table->index(['buyer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wholesale_rfqs');
    }
};