<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Link to the checkpoint (waypoint) and booking/scan
            $table->foreignId('waypoint_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('qr_scan_id')->nullable()->constrained()->nullOnDelete();
            
            $table->enum('media_type', ['image', 'video']);
            $table->string('file_name');
            $table->string('original_path')->nullable(); // if we keep original
            $table->string('optimized_path');
            $table->string('thumbnail_path')->nullable();
            $table->json('metadata')->nullable(); // width, height, duration, etc.
            
            $table->timestamp('captured_at')->nullable(); // when media was taken
            $table->boolean('is_primary')->default(false);
            $table->enum('source', ['user', 'fallback'])->default('user');
            
            $table->timestamps();
            
            $table->index(['user_id', 'waypoint_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_media');
    }
};