<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('provider_id')->constrained();
    $table->foreignId('subscription_id')->nullable()->constrained();
    $table->foreignId('booking_id')->nullable()->constrained();
    $table->string('invoice_number')->unique();
    $table->string('receipt_number')->nullable()->unique();
    $table->decimal('amount', 10, 2);
    $table->string('currency', 3)->default('USD');
    $table->decimal('tax', 10, 2)->default(0);
    $table->decimal('total', 10, 2);
    $table->string('status')->default('pending')->comment('pending, paid, overdue, cancelled');
    $table->string('payment_method')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('due_date')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
