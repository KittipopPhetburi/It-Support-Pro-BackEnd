<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Email Settings
            [
                'category' => 'Email',
                'key' => 'mail_driver',
                'value' => 'smtp',
                'description' => 'ประเภทการส่งอีเมล (smtp, log, sendmail)',
                'type' => 'string',
            ],
            [
                'category' => 'Email',
                'key' => 'smtp_host',
                'value' => 'smtp.gmail.com',
                'description' => 'SMTP Server Host',
                'type' => 'string',
            ],
            [
                'category' => 'Email',
                'key' => 'smtp_port',
                'value' => '587',
                'description' => 'SMTP Port (587 for TLS, 465 for SSL)',
                'type' => 'number',
            ],
            [
                'category' => 'Email',
                'key' => 'smtp_encryption',
                'value' => 'tls',
                'description' => 'การเข้ารหัส (tls, ssl)',
                'type' => 'string',
            ],
            [
                'category' => 'Email',
                'key' => 'smtp_username',
                'value' => '',
                'description' => 'SMTP Username (อีเมลสำหรับส่ง)',
                'type' => 'string',
            ],
            [
                'category' => 'Email',
                'key' => 'smtp_password',
                'value' => '',
                'description' => 'SMTP Password หรือ App Password',
                'type' => 'password',
            ],
            [
                'category' => 'Email',
                'key' => 'mail_from_address',
                'value' => 'noreply@itsupport.com',
                'description' => 'อีเมลผู้ส่ง',
                'type' => 'string',
            ],
            [
                'category' => 'Email',
                'key' => 'mail_from_name',
                'value' => 'IT Support System',
                'description' => 'ชื่อผู้ส่ง',
                'type' => 'string',
            ],
            
            // System Settings
            [
                'category' => 'System',
                'key' => 'auto_close_days',
                'value' => '7',
                'description' => 'จำนวนวันที่จะปิด Incident อัตโนมัติหลังจาก Resolved',
                'type' => 'number',
            ],
            [
                'category' => 'System',
                'key' => 'max_file_size',
                'value' => '10',
                'description' => 'ขนาดไฟล์สูงสุดที่อัปโหลดได้ (MB)',
                'type' => 'number',
            ],
            [
                'category' => 'System',
                'key' => 'session_timeout',
                'value' => '60',
                'description' => 'ระยะเวลา Session (นาที)',
                'type' => 'number',
            ],
            [
                'category' => 'System',
                'key' => 'default_priority',
                'value' => 'Medium',
                'description' => 'ความสำคัญเริ่มต้นสำหรับ Incident ใหม่',
                'type' => 'string',
            ],
            [
                'category' => 'System',
                'key' => 'enable_notifications',
                'value' => 'true',
                'description' => 'เปิดใช้งานการแจ้งเตือน',
                'type' => 'boolean',
            ],
            [
                'category' => 'System',
                'key' => 'maintenance_mode',
                'value' => 'false',
                'description' => 'โหมดบำรุงรักษาระบบ',
                'type' => 'boolean',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['category' => $setting['category'], 'key' => $setting['key']],
                $setting
            );
        }
    }
}
