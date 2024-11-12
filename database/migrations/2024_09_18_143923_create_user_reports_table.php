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
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete(); // المستخدم الذي يقدم البلاغ
            $table->foreignId('reported_id')->constrained('users')->cascadeOnDelete(); // المستخدم الذي تم البلاغ عليه
            $table->foreignId('report_type_id')->constrained()->cascadeOnDelete(); // نوع البلاغ
            $table->boolean('status')->default(0); // حالة البلاغ (0 = قيد المعالجة, 1 = تم التعامل)
            $table->timestamps();

            // منع البلاغات المكررة
            $table->unique(['reporter_id', 'reported_id', 'report_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};
