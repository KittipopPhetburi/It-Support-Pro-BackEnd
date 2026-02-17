<?php

namespace Database\Seeders;

use App\Models\Sla;
use Illuminate\Database\Seeder;

class SlaSeeder extends Seeder
{
    public function run(): void
    {
        $slas = [
            [
                'name' => 'Critical - ฉุกเฉินสูงสุด',
                'priority' => 'Critical',
                'response_time' => 2,
                'resolution_time' => 4,
                'description' => 'สำหรับปัญหาที่ส่งผลกระทบต่อการดำเนินงานหลักของบริษัท',
                'is_active' => true,
            ],
            [
                'name' => 'High - สำคัญมาก',
                'priority' => 'High',
                'response_time' => 4,
                'resolution_time' => 8,
                'description' => 'สำหรับปัญหาที่ส่งผลกระทบต่อการทำงานของหลายคน',
                'is_active' => true,
            ],
            [
                'name' => 'Medium - ปานกลาง',
                'priority' => 'Medium',
                'response_time' => 8,
                'resolution_time' => 12,
                'description' => 'สำหรับปัญหาที่ส่งผลกระทบต่อการทำงานบางส่วน',
                'is_active' => true,
            ],
            [
                'name' => 'Low - ทั่วไป',
                'priority' => 'Low',
                'response_time' => 12,
                'resolution_time' => 24,
                'description' => 'สำหรับปัญหาทั่วไปที่ไม่เร่งด่วน',
                'is_active' => true,
            ],
        ];

        foreach ($slas as $sla) {
            Sla::updateOrCreate(
                ['priority' => $sla['priority']], // Check by priority
                $sla
            );
        }
        
        $this->command?->info('✅ SLAs seeded successfully');
    }
}
