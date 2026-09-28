<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('reference_number')->nullable()->after('payment_id');
            $table->string('receipt_image_path')->nullable()->after('reference_number');
            $table->text('admin_note')->nullable()->after('metadata');
            $table->foreignId('verified_by')->nullable()->after('admin_note')
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');

            $table->index('verified_by');
            $table->index('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropIndex(['verified_by']);
            $table->dropIndex(['verified_at']);
            $table->dropColumn([
                'reference_number', 'receipt_image_path', 'admin_note',
                'verified_by', 'verified_at',
            ]);
        });
    }
};