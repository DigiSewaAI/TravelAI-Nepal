<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sos_alerts', function (Blueprint $table) {
            $table->timestamp('acknowledged_at')->nullable()->after('sent_at');
            $table->foreignId('acknowledged_by')->nullable()->after('acknowledged_at')->constrained('users')->nullOnDelete();

            $table->timestamp('responding_at')->nullable()->after('acknowledged_by');
            $table->foreignId('responding_by')->nullable()->after('responding_at')->constrained('users')->nullOnDelete();

            $table->foreignId('resolved_by')->nullable()->after('resolved_at')->constrained('users')->nullOnDelete();
            $table->string('false_alarm_reason', 500)->nullable()->after('resolved_by');

            $table->timestamp('auto_escalated_at')->nullable()->after('false_alarm_reason');
            $table->timestamp('admin_notified_at')->nullable()->after('auto_escalated_at');

            $table->index('auto_escalated_at');
        });
    }

    public function down(): void
    {
        Schema::table('sos_alerts', function (Blueprint $table) {
            $table->dropForeign(['acknowledged_by']);
            $table->dropForeign(['responding_by']);
            $table->dropForeign(['resolved_by']);
            $table->dropIndex(['auto_escalated_at']);
            $table->dropColumn([
                'acknowledged_at', 'acknowledged_by',
                'responding_at', 'responding_by',
                'resolved_by', 'false_alarm_reason',
                'auto_escalated_at', 'admin_notified_at',
            ]);
        });
    }
};