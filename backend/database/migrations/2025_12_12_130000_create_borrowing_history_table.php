<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * สร้างตาราง borrowing_history สำหรับเก็บประวัติการยืม/เบิก/คืนอุปกรณ์
     */
    public function up(): void
    {
        Schema::create('borrowing_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // ผู้ยืม/เบิก
            $table->string('user_name')->nullable(); // ชื่อผู้ยืม (เก็บไว้กรณี user ถูกลบ)
            $table->string('action_type'); // 'borrow', 'requisition', 'return'
            $table->foreignId('request_id')->nullable(); // อ้างอิง asset_request ถ้ามี
            $table->datetime('action_date'); // วันที่ดำเนินการ
            $table->datetime('expected_return_date')->nullable(); // วันที่กำหนดคืน (สำหรับการยืม)
            $table->datetime('actual_return_date')->nullable(); // วันที่คืนจริง
            $table->text('notes')->nullable(); // หมายเหตุ
            $table->string('status')->default('active'); // 'active', 'returned', 'overdue'
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null'); // ผู้ดำเนินการ
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['asset_id', 'action_date']);
            $table->index(['user_id', 'action_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowing_history');
    }
};
