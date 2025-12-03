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
        Schema::table('holidays', function (Blueprint $table) {
            // ประเภทวันหยุด: public_holiday (วันหยุดราชการ), company_holiday (วันหยุดบริษัท), 
            // sick_leave (ลาป่วย), annual_leave (ลาพักร้อน), personal_leave (ลากิจ), other (อื่นๆ)
            $table->string('type')->default('public_holiday')->after('name');
            $table->text('description')->nullable()->after('type');
            // วันหยุดนี้ใช้กับทุกคน หรือเฉพาะบางคน
            $table->boolean('affects_all')->default(true)->after('description');
            // ถ้า affects_all = false จะเชื่อมกับ user
            $table->foreignId('user_id')->nullable()->after('affects_all')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['type', 'description', 'affects_all', 'user_id']);
        });
    }
};
