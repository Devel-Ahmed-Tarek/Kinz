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
        Schema::create('gift_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // المستخدم الذي اشترى الهدية
            $table->foreignId('gift_id')->constrained()->onDelete('cascade'); // الهدية التي تم شراؤها
            $table->integer('points_spent'); // عدد النقاط المستهلكة
            $table->timestamps(); // وقت الشراء
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_purchases');
    }
};
