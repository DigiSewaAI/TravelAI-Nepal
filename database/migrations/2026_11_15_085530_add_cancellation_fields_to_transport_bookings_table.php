<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_bookings', 'cancellation_status')) {
                $table->enum('cancellation_status', ['pending', 'approved', 'rejected'])->nullable()->after('status');
            }
            if (!Schema::hasColumn('transport_bookings', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancellation_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('transport_bookings', 'cancellation_status')) {
                $table->dropColumn('cancellation_status');
            }
            if (Schema::hasColumn('transport_bookings', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }
        });
    }
};