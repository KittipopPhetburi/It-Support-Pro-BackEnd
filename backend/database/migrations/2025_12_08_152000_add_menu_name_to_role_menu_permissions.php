<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ตรวจสอบว่ามีคอลัมน์ menu_name หรือยัง ถ้ายังไม่มีให้เพิ่ม
        if (!Schema::hasColumn('role_menu_permissions', 'menu_name')) {
            Schema::table('role_menu_permissions', function (Blueprint $table) {
                $table->string('menu_name')->nullable()->after('menu_id')->comment('ชื่อหน้าจอ/เมนู เช่น จัดการผู้ใช้, แจ้งซ่อม');
            });
            
            // อัพเดทข้อมูล menu_name จากตาราง menus
            DB::statement("UPDATE role_menu_permissions rmp 
                          INNER JOIN menus m ON rmp.menu_id = m.id 
                          SET rmp.menu_name = m.name");
        }
    }

    public function down(): void
    {
        Schema::table('role_menu_permissions', function (Blueprint $table) {
            if (Schema::hasColumn('role_menu_permissions', 'menu_name')) {
                $table->dropColumn('menu_name');
            }
        });
    }
};
