<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['text', 'image', 'video']);
            $table->text('content')->nullable(); // URL for image/video, or text content
            $table->string('background_color')->nullable(); // Only for text type
            $table->foreignId('voice_id')->nullable()->constrained('voices')->onDelete('set null'); // Assumes you have a musics table
            $table->enum('video_duration', ['30', '60', '180'])->nullable(); // Only for video type
            $table->enum('visibility', ['public', 'friends', 'private']);
            $table->timestamp('expires_at');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
