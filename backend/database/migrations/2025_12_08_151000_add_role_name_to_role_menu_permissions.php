<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_menu_permissions', function (Blueprint $table) {
            // เพิ่ม role_name, menu_name และ username สำหรับอ่านง่าย
            $table->string('role_name')->nullable()->after('role_id')->comment('ชื่อบทบาท เช่น Admin, Technician');
            $table->string('menu_name')->nullable()->after('menu_id')->comment('ชื่อหน้าจอ/เมนู เช่น จัดการผู้ใช้, แจ้งซ่อม');
            $table->string('created_by_username')->nullable()->comment('ชื่อผู้ใช้ที่สร้าง/แก้ไข');
        });

        // เพิ่ม comment ให้กับคอลัมน์ role_id และ menu_id
        DB::statement("ALTER TABLE role_menu_permissions MODIFY COLUMN role_id BIGINT UNSIGNED NOT NULL COMMENT 'รหัสบทบาท (เชื่อมกับตาราง roles)'");
        DB::statement("ALTER TABLE role_menu_permissions MODIFY COLUMN menu_id BIGINT UNSIGNED NOT NULL COMMENT 'รหัสเมนู (เชื่อมกับตาราง menus)'");
        
        // อัพเดทข้อมูล role_name และ menu_name จากตาราง roles และ menus
        DB::statement("UPDATE role_menu_permissions rmp 
                      INNER JOIN roles r ON rmp.role_id = r.id 
                      SET rmp.role_name = COALESCE(r.display_name, r.name)");
        
        DB::statement("UPDATE role_menu_permissions rmp 
                      INNER JOIN menus m ON rmp.menu_id = m.id 
                      SET rmp.menu_name = m.name");
    }

    public function down(): void
    {
        Schema::table('role_menu_permissions', function (Blueprint $table) {
            $table->dropColumn(['role_name', 'menu_name', 'created_by_username']);
        });

        DB::statement("ALTER TABLE role_menu_permissions MODIFY COLUMN role_id BIGINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE role_menu_permissions MODIFY COLUMN menu_id BIGINT UNSIGNED NOT NULL");
    }
};
