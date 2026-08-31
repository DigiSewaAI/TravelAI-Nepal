<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('safety_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('travel_safety_incidents')->onDelete('cascade');
            $table->string('action'); // created, updated, verified, overridden, merged, resolved, expired, etc.
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['incident_id', 'action']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('safety_audit_logs');
    }
};