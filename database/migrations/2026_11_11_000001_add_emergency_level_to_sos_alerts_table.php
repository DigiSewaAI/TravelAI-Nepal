<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasColumn('sos_alerts', 'emergency_level')) {
            return;
        }
        Schema::table('sos_alerts', function (Blueprint $table) {
            $table->enum('emergency_level', ['critical','urgent','assistance','unclassified'])
                  ->nullable()->after('status')
                  ->comment('NULL = not yet classified (historical)');
            $table->timestamp('level_changed_at')->nullable()->after('emergency_level');
            $table->foreignId('level_changed_by')->nullable()->after('level_changed_at')
                  ->constrained('users')->nullOnDelete();
            $table->string('cancel_reason', 500)->nullable()->after('false_alarm_reason');
        });
    }

    public function down(): void {
        Schema::table('sos_alerts', function (Blueprint $table) {
            if (Schema::hasColumn('sos_alerts', 'level_changed_by')) {
                $table->dropForeign(['level_changed_by']);
            }
            $table->dropColumn(['emergency_level', 'level_changed_at', 'level_changed_by', 'cancel_reason']);
        });
    }
};