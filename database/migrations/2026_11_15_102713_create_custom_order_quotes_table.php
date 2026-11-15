<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_order_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_order_id')->constrained('custom_orders')->onDelete('cascade');
            $table->integer('version')->default(1);
            $table->decimal('quoted_amount', 10, 2);
            $table->string('currency', 3)->default('NPR');
            $table->text('materials')->nullable();
            $table->string('dimensions')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('production_days')->nullable();
            $table->text('delivery_terms')->nullable();
            $table->decimal('advance_proposal', 10, 2)->nullable();
            $table->date('valid_until')->nullable();
            $table->text('provider_message')->nullable();
            $table->enum('status', ['pending','accepted','rejected','superseded'])->default('pending');
            $table->text('customer_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['custom_order_id', 'version'], 'coq_order_version_unique');
            $table->index(['custom_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_order_quotes');
    }
};