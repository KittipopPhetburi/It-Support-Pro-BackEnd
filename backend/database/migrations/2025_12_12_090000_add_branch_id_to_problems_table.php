<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * เพิ่ม branch_id ใน problems table เพื่อรองรับการแยกข้อมูลตามสาขา
     */
    public function up(): void
    {
        Schema::table('problems', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('solution')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('problems', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
