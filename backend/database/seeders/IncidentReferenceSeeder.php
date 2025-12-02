<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncidentReferenceSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- ประเภท Incident ----------
        DB::table('incident_categories')->insert([
            [
                'id' => 1,
                'name' => 'ทั่วไป',
                'description' => 'ปัญหาทั่วไป',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'ระบบเครือข่าย',
                'description' => 'Network / Internet',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ---------- ความสำคัญ ----------
        DB::table('incident_priorities')->insert([
            [
                'id' => 1,
                'name' => 'Low',
                'description' => 'งานไม่เร่งด่วน',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Medium',
                'description' => 'งานปานกลาง',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'High',
                'description' => 'งานเร่งด่วน',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ---------- สถานะ ----------
        DB::table('incident_statuses')->insert([
            [
                'id' => 1,
                'name' => 'เปิดใหม่',
                'description' => 'สร้าง Ticket ใหม่',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'กำลังดำเนินการ',
                'description' => 'อยู่ระหว่างแก้ไข',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'ปิดงาน',
                'description' => 'แก้ไขเสร็จแล้ว',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
