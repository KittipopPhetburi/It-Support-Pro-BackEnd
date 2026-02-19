<?php

namespace Database\Seeders;

use App\Models\IncidentTitle;
use Illuminate\Database\Seeder;

class IncidentTitleSeeder extends Seeder
{
    public function run(): void
    {
        $titles = [
            // Hardware - Critical
            ['title' => 'Server Down', 'category' => 'Hardware', 'priority' => 'Critical', 'response_time' => 1, 'resolution_time' => 2],
            ['title' => 'ระบบเครือข่ายล่ม', 'category' => 'Hardware', 'priority' => 'Critical', 'response_time' => 1, 'resolution_time' => 2],
            
            // Hardware - High
            ['title' => 'เครื่องคอมพิวเตอร์เสีย', 'category' => 'Hardware', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            ['title' => 'เครื่องพิมพ์ไม่ทำงาน', 'category' => 'Hardware', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            ['title' => 'Monitor ไม่แสดงผล', 'category' => 'Hardware', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            
            // Hardware - Medium
            ['title' => 'คีย์บอร์ด/เมาส์เสีย', 'category' => 'Hardware', 'priority' => 'Medium', 'response_time' => 4, 'resolution_time' => 8],
            ['title' => 'ลำโพง/หูฟังไม่มีเสียง', 'category' => 'Hardware', 'priority' => 'Medium', 'response_time' => 4, 'resolution_time' => 8],
            ['title' => 'ต้องการเปลี่ยนอุปกรณ์ใหม่', 'category' => 'Hardware', 'priority' => 'Medium', 'response_time' => 4, 'resolution_time' => 8],
            
            // Hardware - Low
            ['title' => 'ต้องการติดตั้งอุปกรณ์เพิ่มเติม', 'category' => 'Hardware', 'priority' => 'Low', 'response_time' => 8, 'resolution_time' => 16],
            
            // Software - Critical
            ['title' => 'ระบบหลักไม่สามารถใช้งานได้', 'category' => 'Software', 'priority' => 'Critical', 'response_time' => 1, 'resolution_time' => 2],
            ['title' => 'ข้อมูลสูญหาย', 'category' => 'Software', 'priority' => 'Critical', 'response_time' => 1, 'resolution_time' => 2],
            
            // Software - High
            ['title' => 'โปรแกรมทำงานผิดพลาด', 'category' => 'Software', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            ['title' => 'ไม่สามารถเปิดไฟล์งานได้', 'category' => 'Software', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            ['title' => 'โปรแกรม Office มีปัญหา', 'category' => 'Software', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            
            // Software - Medium
            ['title' => 'ต้องการติดตั้งโปรแกรมใหม่', 'category' => 'Software', 'priority' => 'Medium', 'response_time' => 4, 'resolution_time' => 8],
            ['title' => 'โปรแกรมทำงานช้า', 'category' => 'Software', 'priority' => 'Medium', 'response_time' => 4, 'resolution_time' => 8],
            ['title' => 'ต้องการอัปเดตโปรแกรม', 'category' => 'Software', 'priority' => 'Medium', 'response_time' => 4, 'resolution_time' => 8],
            
            // Software - Low
            ['title' => 'สอบถามการใช้งานโปรแกรม', 'category' => 'Software', 'priority' => 'Low', 'response_time' => 8, 'resolution_time' => 16],
            
            // Network - Critical
            ['title' => 'Internet ขาดการเชื่อมต่อทั้งหมด', 'category' => 'Network', 'priority' => 'Critical', 'response_time' => 1, 'resolution_time' => 2],
            ['title' => 'VPN ไม่สามารถเชื่อมต่อได้', 'category' => 'Network', 'priority' => 'Critical', 'response_time' => 1, 'resolution_time' => 2],
            
            // Network - High
            ['title' => 'WiFi ไม่มีสัญญาณ', 'category' => 'Network', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            ['title' => 'Internet ช้ามาก', 'category' => 'Network', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            ['title' => 'ไม่สามารถเข้าถึง Network Drive', 'category' => 'Network', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            
            // Network - Medium
            ['title' => 'WiFi หลุดบ่อย', 'category' => 'Network', 'priority' => 'Medium', 'response_time' => 4, 'resolution_time' => 8],
            ['title' => 'ต้องการตั้งค่า Network', 'category' => 'Network', 'priority' => 'Medium', 'response_time' => 4, 'resolution_time' => 8],
            
            // Account - Critical
            ['title' => 'ไม่สามารถ Login ระบบหลักได้', 'category' => 'Account', 'priority' => 'Critical', 'response_time' => 1, 'resolution_time' => 2],
            ['title' => 'Account ถูกล็อค', 'category' => 'Account', 'priority' => 'Critical', 'response_time' => 1, 'resolution_time' => 2],
            
            // Account - High
            ['title' => 'ลืมรหัสผ่าน', 'category' => 'Account', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            ['title' => 'ต้องการเปลี่ยนรหัสผ่าน', 'category' => 'Account', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            ['title' => 'สิทธิ์การเข้าถึงไม่ถูกต้อง', 'category' => 'Account', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            
            // Account - Medium
            ['title' => 'ต้องการสร้าง Account ใหม่', 'category' => 'Account', 'priority' => 'Medium', 'response_time' => 4, 'resolution_time' => 8],
            ['title' => 'ต้องการแก้ไขข้อมูล Profile', 'category' => 'Account', 'priority' => 'Medium', 'response_time' => 4, 'resolution_time' => 8],
            
            // Email - Critical
            ['title' => 'Email ไม่สามารถส่ง-รับได้', 'category' => 'Email', 'priority' => 'Critical', 'response_time' => 1, 'resolution_time' => 2],
            ['title' => 'Email เต็ม ไม่สามารถรับเมลใหม่', 'category' => 'Email', 'priority' => 'Critical', 'response_time' => 1, 'resolution_time' => 2],
            
            // Email - High
            ['title' => 'Email หายไป', 'category' => 'Email', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            ['title' => 'ต้องการกู้คืน Email ที่ลบไป', 'category' => 'Email', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            ['title' => 'ไม่สามารถส่งไฟล์แนบได้', 'category' => 'Email', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            
            // Email - Medium
            ['title' => 'ต้องการตั้งค่า Email บนมือถือ', 'category' => 'Email', 'priority' => 'Medium', 'response_time' => 4, 'resolution_time' => 8],
            ['title' => 'ต้องการเพิ่ม Signature', 'category' => 'Email', 'priority' => 'Medium', 'response_time' => 4, 'resolution_time' => 8],
            
            // Security - Critical
            ['title' => 'ต้องการแจ้ง Security Incident', 'category' => 'Security', 'priority' => 'Critical', 'response_time' => 1, 'resolution_time' => 2],
            ['title' => 'สงสัยถูกไวรัส / Malware', 'category' => 'Security', 'priority' => 'Critical', 'response_time' => 1, 'resolution_time' => 2],
            ['title' => 'พบ Phishing Email', 'category' => 'Security', 'priority' => 'Critical', 'response_time' => 1, 'resolution_time' => 2],
            
            // Security - High
            ['title' => 'Antivirus ไม่ทำงาน', 'category' => 'Security', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            ['title' => 'ปัญหาการเข้าถึงข้อมูล', 'category' => 'Security', 'priority' => 'High', 'response_time' => 2, 'resolution_time' => 4],
            
            // Other - Medium (default)
            ['title' => 'อื่นๆ ที่ไม่ระบุ', 'category' => 'Other', 'priority' => 'Medium', 'response_time' => 4, 'resolution_time' => 8],
        ];

        foreach ($titles as $title) {
            IncidentTitle::updateOrCreate(
                ['title' => $title['title']], // Check existing by title
                [
                    'category' => $title['category'],
                    'priority' => $title['priority'],
                    'response_time' => $title['response_time'],
                    'resolution_time' => $title['resolution_time'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ Incident titles seeded successfully');
    }
}
