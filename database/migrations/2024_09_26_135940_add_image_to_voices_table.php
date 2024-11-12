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
        Schema::table('voices', function (Blueprint $table) {
            $table->string('image')->nullable()->after('voice'); // إضافة حقل الصورة
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voices', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
