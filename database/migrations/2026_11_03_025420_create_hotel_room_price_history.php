<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('hotel_room_price_history')) return;
        Schema::create('hotel_room_price_history', function (Blueprint $t) {
            $t->id();
            $t->foreignId('room_id')->constrained('hotel_rooms')->cascadeOnDelete();
            $t->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $t->decimal('old_price', 10, 2)->nullable();
            $t->decimal('new_price', 10, 2);
            $t->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('reason', 100)->nullable();
            $t->timestamps();
            $t->index(['provider_id', 'created_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('hotel_room_price_history');
    }
};