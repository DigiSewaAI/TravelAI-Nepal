<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->string('link_url', 500);
            $table->string('link_target', 10)->default('_blank');
            $table->string('cta_text')->nullable();

            // Payment/pricing
            $table->integer('duration_days');
            $table->integer('price_paid');
            $table->enum('payment_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->string('payment_proof_path')->nullable();
            $table->timestamp('payment_submitted_at')->nullable();
            $table->timestamp('payment_verified_at')->nullable();

            // Approval workflow
            $table->enum('status', [
                'payment_pending',
                'pending_review',
                'approved',
                'active',
                'rejected',
                'expired',
            ])->default('payment_pending');

            // Scheduling
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();

            // Targeting (Phase 2 — schema only)
            $table->string('target_destination')->nullable();
            $table->unsignedBigInteger('target_category_id')->nullable();

            // Placement (MVP = 1 fixed)
            $table->string('placement', 30)->default('dashboard_featured');

            // Display ordering
            $table->integer('sort_order')->default(0);

            // Analytics (denormalized)
            $table->integer('impressions')->default(0);
            $table->integer('clicks')->default(0);

            // Admin actions
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'placement', 'sort_order']);
            $table->index(['provider_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};