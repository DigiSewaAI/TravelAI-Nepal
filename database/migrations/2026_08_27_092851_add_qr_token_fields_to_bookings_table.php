<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // ✅ Token for secure QR (HMAC)
            $table->string('qr_token', 64)->nullable()->after('qr_code');
            
            // ✅ Token expiry (default: 30 days from booking creation)
            $table->timestamp('qr_token_expires_at')->nullable()->after('qr_token');
            
            // ✅ Index for faster lookups
            $table->index('qr_token');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['qr_token', 'qr_token_expires_at']);
        });
    }
};