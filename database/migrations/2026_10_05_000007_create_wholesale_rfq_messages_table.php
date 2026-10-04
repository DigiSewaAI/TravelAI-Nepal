<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wholesale_rfq_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('wholesale_rfqs')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->enum('sender_role', ['buyer', 'provider']);
            $table->text('message');
            $table->timestamps();

            $table->index(['rfq_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wholesale_rfq_messages');
    }
};