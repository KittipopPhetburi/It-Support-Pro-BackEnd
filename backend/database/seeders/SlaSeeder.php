<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sla;

class SlaSeeder extends Seeder
{
    public function run(): void
    {
        $slas = [
            [
                'name' => 'Critical - ฉุกเฉินสูงสุด',
                'priority' => 'Critical',
                'response_time' => 1, // 1 hour
                'resolution_time' => 4, // 4 hours
                'description' => 'สำหรับปัญหาที่ส่งผลกระทบต่อระบบหลักและต้องการการแก้ไขทันที',
                'is_active' => true,
            ],
            [
                'name' => 'High - สำคัญมาก',
                'priority' => 'High',
                'response_time' => 2, // 2 hours
                'resolution_time' => 8, // 8 hours
                'description' => 'สำหรับปัญหาที่ส่งผลกระทบต่อการทำงานของผู้ใช้หลายคน',
                'is_active' => true,
            ],
            [
                'name' => 'Medium - ปานกลาง',
                'priority' => 'Medium',
                'response_time' => 8, // 8 hours
                'resolution_time' => 24, // 24 hours (1 day)
                'description' => 'สำหรับปัญหาทั่วไปที่ส่งผลกระทบต่อการทำงานแต่มีวิธีแก้ไขชั่วคราว',
                'is_active' => true,
            ],
            [
                'name' => 'Low - ทั่วไป',
                'priority' => 'Low',
                'response_time' => 24, // 24 hours
                'resolution_time' => 72, // 72 hours (3 days)
                'description' => 'สำหรับคำถามทั่วไปหรือคำขอที่ไม่มีความเร่งด่วน',
                'is_active' => true,
            ],
        ];

        foreach ($slas as $sla) {
            Sla::updateOrCreate(
                ['priority' => $sla['priority']],
                $sla
            );
        }
    }
}
