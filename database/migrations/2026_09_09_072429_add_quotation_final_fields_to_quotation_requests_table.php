<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            // Provider edited final version (JSON) – same structure as quotation_data
            $table->json('quotation_final')->nullable()->after('quotation_text');
            
            // Quotation lifecycle status (independent from request status)
            $table->enum('quotation_status', ['draft', 'reviewed', 'edited', 'sent'])
                  ->default('draft')
                  ->after('status');
            
            // Timestamps
            $table->timestamp('edited_at')->nullable()->after('quotation_status');
            $table->timestamp('sent_at')->nullable()->after('edited_at');
            
            // Who edited (User ID, not Provider ID)
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete()->after('sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->dropColumn(['quotation_final', 'quotation_status', 'edited_at', 'sent_at', 'edited_by']);
        });
    }
};