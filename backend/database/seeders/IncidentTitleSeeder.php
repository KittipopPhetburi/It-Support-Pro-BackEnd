<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IncidentTitle;

class IncidentTitleSeeder extends Seeder
{
    public function run()
    {
        $titles = [
            // Hardware
            ['category' => 'Hardware', 'title' => 'คอมพิวเตอร์เปิดไม่ติด', 'priority' => 'High', 'response_time' => 4, 'resolution_time' => 8],
            ['category' => 'Hardware', 'title' => 'หน้าจอไม่แสดงผล', 'priority' => 'High', 'response_time' => 4, 'resolution_time' => 8],
            ['category' => 'Hardware', 'title' => 'คีย์บอร์ดหรือเมาส์ใช้งานไม่ได้', 'priority' => 'Medium', 'response_time' => 8, 'resolution_time' => 12],
            ['category' => 'Hardware', 'title' => 'เครื่องพิมพ์ไม่ทำงาน / ติดกระดาษ', 'priority' => 'Medium', 'response_time' => 8, 'resolution_time' => 12],
            ['category' => 'Hardware', 'title' => 'เสียงไม่ออก / ไมค์ใช้ไม่ได้', 'priority' => 'Low', 'response_time' => 12, 'resolution_time' => 24],
            ['category' => 'Hardware', 'title' => 'ฮาร์ดดิสก์เต็ม / มีเสียงผิดปกติ', 'priority' => 'High', 'response_time' => 4, 'resolution_time' => 8],
            ['category' => 'Hardware', 'title' => 'เครื่องทำงานช้า / ค้าง', 'priority' => 'Medium', 'response_time' => 8, 'resolution_time' => 12],
            
            // Software
            ['category' => 'Software', 'title' => 'โปรแกรมเปิดไม่ติด / ใช้งานไม่ได้', 'priority' => 'High', 'response_time' => 4, 'resolution_time' => 8],
            ['category' => 'Software', 'title' => 'โปรแกรม Crash / Error', 'priority' => 'High', 'response_time' => 4, 'resolution_time' => 8],
            ['category' => 'Software', 'title' => 'License หมดอายุ / ไม่สามารถ Activate', 'priority' => 'Medium', 'response_time' => 8, 'resolution_time' => 12],
            ['category' => 'Software', 'title' => 'ต้องการติดตั้งโปรแกรมเพิ่มเติม', 'priority' => 'Low', 'response_time' => 12, 'resolution_time' => 24],
            ['category' => 'Software', 'title' => 'อัพเดตโปรแกรมไม่ได้', 'priority' => 'Medium', 'response_time' => 8, 'resolution_time' => 12],
            ['category' => 'Software', 'title' => 'ปัญหาการใช้งาน Microsoft Office', 'priority' => 'Medium', 'response_time' => 8, 'resolution_time' => 12],
            
            // Network
            ['category' => 'Network', 'title' => 'ไม่สามารถเชื่อมต่ออินเทอร์เน็ต', 'priority' => 'Critical', 'response_time' => 2, 'resolution_time' => 4],
            ['category' => 'Network', 'title' => 'WiFi เชื่อมต่อไม่ได้', 'priority' => 'High', 'response_time' => 4, 'resolution_time' => 8],
            ['category' => 'Network', 'title' => 'อินเทอร์เน็ตช้า', 'priority' => 'Medium', 'response_time' => 8, 'resolution_time' => 12],
            ['category' => 'Network', 'title' => 'ไม่สามารถเข้าถึงเซิร์ฟเวอร์', 'priority' => 'Critical', 'response_time' => 2, 'resolution_time' => 4],
            ['category' => 'Network', 'title' => 'VPN เชื่อมต่อไม่ได้', 'priority' => 'High', 'response_time' => 4, 'resolution_time' => 8],
            ['category' => 'Network', 'title' => 'ปัญหา Network Printer', 'priority' => 'Medium', 'response_time' => 8, 'resolution_time' => 12],
            
            // Account
            ['category' => 'Account', 'title' => 'ลืมรหัสผ่าน', 'priority' => 'High', 'response_time' => 4, 'resolution_time' => 8],
            ['category' => 'Account', 'title' => 'ไม่สามารถเข้าสู่ระบบ', 'priority' => 'Critical', 'response_time' => 2, 'resolution_time' => 4],
            ['category' => 'Account', 'title' => 'ต้องการสร้าง Account ใหม่', 'priority' => 'Medium', 'response_time' => 8, 'resolution_time' => 12],
            ['category' => 'Account', 'title' => 'ต้องการเปลี่ยนแปลงสิทธิ์การใช้งาน', 'priority' => 'Medium', 'response_time' => 8, 'resolution_time' => 12],
            ['category' => 'Account', 'title' => 'Account ถูกล็อค', 'priority' => 'Critical', 'response_time' => 2, 'resolution_time' => 4],
            
            // Email
            ['category' => 'Email', 'title' => 'ส่ง-รับอีเมลไม่ได้', 'priority' => 'High', 'response_time' => 4, 'resolution_time' => 8],
            ['category' => 'Email', 'title' => 'อีเมลเต็ม / ไม่มีพื้นที่', 'priority' => 'Medium', 'response_time' => 8, 'resolution_time' => 12],
            ['category' => 'Email', 'title' => 'อีเมลถูกส่งไป Spam', 'priority' => 'Low', 'response_time' => 12, 'resolution_time' => 24],
            ['category' => 'Email', 'title' => 'ตั้งค่า Email บนมือถือไม่ได้', 'priority' => 'Medium', 'response_time' => 8, 'resolution_time' => 12],
            ['category' => 'Email', 'title' => 'ปัญหา Signature อีเมล', 'priority' => 'Low', 'response_time' => 12, 'resolution_time' => 24],
            
            // Security
            ['category' => 'Security', 'title' => 'สงสัยถูกไวรัส / Malware', 'priority' => 'Critical', 'response_time' => 2, 'resolution_time' => 4],
            ['category' => 'Security', 'title' => 'Antivirus ไม่ทำงาน', 'priority' => 'Critical', 'response_time' => 2, 'resolution_time' => 4],
            ['category' => 'Security', 'title' => 'พบ Phishing Email', 'priority' => 'Critical', 'response_time' => 2, 'resolution_time' => 4],
            ['category' => 'Security', 'title' => 'ปัญหาการเข้าถึงข้อมูล', 'priority' => 'High', 'response_time' => 4, 'resolution_time' => 8],
        ];

        foreach ($titles as $title) {
            IncidentTitle::create(array_merge($title, ['is_active' => true]));
        }
    }
}
