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
        Schema::create('bans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // المستخدم المحظور
            $table->enum('ban_type', ['temporary', 'permanent'])->default('temporary'); // نوع الحظر
            $table->timestamp('banned_at')->useCurrent(); // تاريخ الحظر
            $table->timestamp('unbanned_at')->nullable(); // تاريخ فك الحظر في حالة الحظر المؤقت
            $table->text('reason')->nullable(); // سبب الحظر
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bans');
    }
};
