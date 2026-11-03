<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('suppliers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('phone', 50)->nullable();
            $t->string('email')->nullable();
            $t->string('address', 500)->nullable();
            $t->text('notes')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['provider_id', 'is_active']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('suppliers');
    }
};