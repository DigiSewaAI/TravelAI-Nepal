<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_cancellation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_booking_id')->constrained('transport_bookings')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->text('reason');
            $table->decimal('estimated_refund', 10, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('refund_status', ['none', 'pending', 'refunded'])->default('none');
            $table->text('provider_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('transport_booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_cancellation_requests');
    }
};