<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('sos_level_history')) return;
        Schema::create('sos_level_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sos_alert_id');
            $table->enum('from_level', ['critical','urgent','assistance','unclassified'])->nullable();
            $table->enum('to_level',   ['critical','urgent','assistance','unclassified']);
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('reason', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('sos_alert_id');
            $table->index(['sos_alert_id', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('sos_level_history');
    }
};