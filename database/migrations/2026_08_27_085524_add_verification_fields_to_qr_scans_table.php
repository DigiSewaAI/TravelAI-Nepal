<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_scans', function (Blueprint $table) {
            // ✅ Verification status
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])
                  ->default('pending')
                  ->after('waypoint_id');

            // ✅ Duplicate reference (self-referencing foreign key)
            $table->foreignId('duplicate_of')
                  ->nullable()
                  ->constrained('qr_scans')
                  ->nullOnDelete()
                  ->after('verification_status');

            // ✅ Who verified and when
            $table->foreignId('verified_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->after('duplicate_of');

            $table->timestamp('verified_at')->nullable()->after('verified_by');

            // ✅ Indexes for performance
            $table->index('verification_status');
            $table->index('duplicate_of');
        });
    }

    public function down(): void
    {
        Schema::table('qr_scans', function (Blueprint $table) {
            $table->dropForeign(['duplicate_of']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'verification_status',
                'duplicate_of',
                'verified_by',
                'verified_at'
            ]);
        });
    }
};