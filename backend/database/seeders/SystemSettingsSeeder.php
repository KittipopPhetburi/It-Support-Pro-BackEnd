<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['category' => 'SLA', 'key' => 'sla_response_critical', 'value' => '1', 'description' => 'Response time for Critical priority incidents (hours)'],
            ['category' => 'SLA', 'key' => 'sla_response_high', 'value' => '4', 'description' => 'Response time for High priority incidents (hours)'],
            ['category' => 'SLA', 'key' => 'sla_response_medium', 'value' => '8', 'description' => 'Response time for Medium priority incidents (hours)'],
            ['category' => 'SLA', 'key' => 'sla_response_low', 'value' => '24', 'description' => 'Response time for Low priority incidents (hours)'],
            ['category' => 'Email', 'key' => 'mail_driver', 'value' => 'log', 'description' => 'Mail Driver (smtp/log)'],
            ['category' => 'Email', 'key' => 'smtp_host', 'value' => 'smtp.gmail.com', 'description' => 'SMTP Server สำหรับส่งอีเมล'],
            ['category' => 'Email', 'key' => 'smtp_port', 'value' => '587', 'description' => 'Port สำหรับ SMTP'],
            ['category' => 'Email', 'key' => 'smtp_username', 'value' => '', 'description' => 'Username/Email สำหรับ SMTP'],
            ['category' => 'Email', 'key' => 'smtp_password', 'value' => '', 'description' => 'Password/App Password สำหรับ SMTP'],
            ['category' => 'Email', 'key' => 'smtp_encryption', 'value' => 'tls', 'description' => 'Encryption (tls/ssl)'],
            ['category' => 'Email', 'key' => 'mail_from_address', 'value' => 'noreply@itsupport.com', 'description' => 'Email ผู้ส่ง'],
            ['category' => 'Email', 'key' => 'mail_from_name', 'value' => 'IT Support System', 'description' => 'ชื่อผู้ส่ง'],
            ['category' => 'System', 'key' => 'auto_assign', 'value' => 'true', 'description' => 'อนุญาตให้มอบหมาย Incident อัตโนมัติ'],
            ['category' => 'System', 'key' => 'max_file_size', 'value' => '10', 'description' => 'ขนาดไฟล์แนบสูงสุด (MB)'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['category' => $setting['category'], 'key' => $setting['key']],
                $setting
            );
        }
    }
}
