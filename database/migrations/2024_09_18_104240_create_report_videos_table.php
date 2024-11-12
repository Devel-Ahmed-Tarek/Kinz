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
        Schema::create('report_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_type_id')->constrained()->cascadeOnDelete();
            $table->boolean('status')->default(0);
            $table->timestamps();

            // Example: Adding a unique constraint to avoid duplicate reports for the same video by the same user
            $table->unique(['user_id', 'video_id', 'report_type_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_videos');
    }
};
