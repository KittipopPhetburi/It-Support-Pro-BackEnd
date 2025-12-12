<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * อัปเดต organization ของ assets จาก branch_id (สำหรับ assets ที่มี branch_id แต่ไม่มี organization)
     */
    public function up(): void
    {
        // ดึงรายชื่อ branches ทั้งหมด
        $branches = DB::table('branches')->get();
        
        foreach ($branches as $branch) {
            // อัปเดต assets ที่มี branch_id ตรงกับ branch แต่ไม่มี organization หรือ organization ว่าง
            DB::table('assets')
                ->where('branch_id', $branch->id)
                ->where(function($query) {
                    $query->whereNull('organization')
                          ->orWhere('organization', '');
                })
                ->update(['organization' => $branch->name]);
        }
        
        // Log ผลลัพธ์
        $updatedCount = DB::table('assets')->whereNotNull('organization')->where('organization', '!=', '')->count();
        \Log::info("Updated assets organization from branch_id. Total with organization: {$updatedCount}");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ไม่ต้อง rollback
    }
};
