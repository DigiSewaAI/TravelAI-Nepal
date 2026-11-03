<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('folio_charges', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hotel_booking_id')->constrained('hotel_bookings')->cascadeOnDelete();
            $t->string('description');
            $t->string('category', 50)->default('other'); // room, food, laundry, extra
            $t->decimal('amount', 10, 2);
            $t->integer('quantity')->default(1);
            $t->decimal('total', 10, 2);
            $t->text('notes')->nullable();
            $t->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index('hotel_booking_id');
        });
    }
    public function down(): void { Schema::dropIfExists('folio_charges'); }
};