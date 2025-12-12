<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * อัปเดต branch_id ของ assets ทุกรายการให้ตรงกับชื่อ organization
     */
    public function up(): void
    {
        // ดึงรายชื่อ branches ทั้งหมด
        $branches = DB::table('branches')->get();
        
        foreach ($branches as $branch) {
            // อัปเดต assets ที่มี organization ตรงกับชื่อ branch
            DB::table('assets')
                ->where('organization', $branch->name)
                ->update(['branch_id' => $branch->id]);
        }
        
        // Log ผลลัพธ์
        $updatedCount = DB::table('assets')->whereNotNull('branch_id')->count();
        \Log::info("Updated {$updatedCount} assets with branch_id based on organization name");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ไม่ต้อง rollback เพราะเป็นการอัปเดตข้อมูลให้ถูกต้อง
    }
};
