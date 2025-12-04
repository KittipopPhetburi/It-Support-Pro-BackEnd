<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * เพิ่มฟิลด์สำหรับเก็บ log ที่ครอบคลุมมากขึ้น
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // ข้อมูลระบบ (System Logs)
            $table->enum('severity', ['INFO', 'WARN', 'ERROR', 'CRITICAL'])->default('INFO')->after('action');
            $table->string('event_type')->nullable()->after('severity'); // ประเภทเหตุการณ์
            
            // ข้อมูลการเชื่อมต่อ
            $table->string('ip_address', 45)->nullable()->after('details'); // รองรับ IPv6
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->string('session_id')->nullable()->after('user_agent');
            
            // ข้อมูลเป้าหมาย
            $table->string('target_type')->nullable()->after('session_id'); // Incident, Asset, User, etc.
            $table->string('target_id')->nullable()->after('target_type');
            $table->string('target_name')->nullable()->after('target_id');
            
            // ข้อมูลการเปลี่ยนแปลง
            $table->text('old_value')->nullable()->after('target_name');
            $table->text('new_value')->nullable()->after('old_value');
            
            // ข้อมูล Request/Response
            $table->string('request_method')->nullable()->after('new_value'); // GET, POST, PUT, DELETE
            $table->string('request_url')->nullable()->after('request_method');
            $table->integer('response_status')->nullable()->after('request_url'); // HTTP status code
            $table->integer('response_time')->nullable()->after('response_status'); // milliseconds
            
            // ข้อมูลผู้ใช้เพิ่มเติม
            $table->string('user_role')->nullable()->after('user_id');
            $table->string('user_email')->nullable()->after('user_role');
            
            // ข้อมูลอุปกรณ์/เบราว์เซอร์
            $table->string('device_type')->nullable()->after('user_agent'); // desktop, mobile, tablet
            $table->string('browser')->nullable()->after('device_type');
            $table->string('os')->nullable()->after('browser');
            
            // Indexes สำหรับการค้นหาที่รวดเร็ว
            $table->index('severity');
            $table->index('event_type');
            $table->index('ip_address');
            $table->index('target_type');
            $table->index('target_id');
            $table->index(['user_id', 'timestamp']);
            $table->index(['action', 'timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['severity']);
            $table->dropIndex(['event_type']);
            $table->dropIndex(['ip_address']);
            $table->dropIndex(['target_type']);
            $table->dropIndex(['target_id']);
            $table->dropIndex(['user_id', 'timestamp']);
            $table->dropIndex(['action', 'timestamp']);
            
            $table->dropColumn([
                'severity',
                'event_type',
                'ip_address',
                'user_agent',
                'session_id',
                'target_type',
                'target_id',
                'target_name',
                'old_value',
                'new_value',
                'request_method',
                'request_url',
                'response_status',
                'response_time',
                'user_role',
                'user_email',
                'device_type',
                'browser',
                'os',
            ]);
        });
    }
};
