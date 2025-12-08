<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // เพิ่ม comment ให้กับคอลัมน์เดิม
        DB::statement("ALTER TABLE role_menu_permissions MODIFY COLUMN can_view TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'สิทธิ์ดู: เห็นหน้านี้และดูข้อมูลได้'");
        DB::statement("ALTER TABLE role_menu_permissions MODIFY COLUMN can_create TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'สิทธิ์เพิ่ม: สร้างรายการใหม่ได้'");
        DB::statement("ALTER TABLE role_menu_permissions MODIFY COLUMN can_update TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'สิทธิ์แก้ไข: แก้ไขข้อมูลที่มีอยู่ได้'");
        DB::statement("ALTER TABLE role_menu_permissions MODIFY COLUMN can_delete TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'สิทธิ์ลบ: ลบข้อมูลออกจากระบบได้'");
    }

    public function down(): void
    {
        // ลบ comment (กลับเป็นไม่มี comment)
        DB::statement("ALTER TABLE role_menu_permissions MODIFY COLUMN can_view TINYINT(1) NOT NULL DEFAULT 0");
        DB::statement("ALTER TABLE role_menu_permissions MODIFY COLUMN can_create TINYINT(1) NOT NULL DEFAULT 0");
        DB::statement("ALTER TABLE role_menu_permissions MODIFY COLUMN can_update TINYINT(1) NOT NULL DEFAULT 0");
        DB::statement("ALTER TABLE role_menu_permissions MODIFY COLUMN can_delete TINYINT(1) NOT NULL DEFAULT 0");
    }
};
