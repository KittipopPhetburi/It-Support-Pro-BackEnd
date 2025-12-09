<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\User;
use App\Models\Incident;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class ExampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Seed 6 assets (ชื่อจริง)
        $assetData = [
            ['name' => 'คอมพิวเตอร์ HP EliteBook', 'type' => 'Notebook', 'serial_number' => 'HP-ELITE-001', 'status' => 'Available', 'location' => 'สำนักงานใหญ่'],
            ['name' => 'ปริ้นเตอร์ Canon LBP6030', 'type' => 'Printer', 'serial_number' => 'CANON-PRT-002', 'status' => 'In Use', 'location' => 'สาขาบางนา'],
            ['name' => 'โน้ตบุ๊ค Dell Inspiron', 'type' => 'Notebook', 'serial_number' => 'DELL-INS-003', 'status' => 'Maintenance', 'location' => 'สาขารังสิต'],
            ['name' => 'Switch Cisco SG300', 'type' => 'Network', 'serial_number' => 'CISCO-SW-004', 'status' => 'Available', 'location' => 'สาขาชลบุรี'],
            ['name' => 'จอมอนิเตอร์ Samsung 24"', 'type' => 'Monitor', 'serial_number' => 'SAMSUNG-MON-005', 'status' => 'Retired', 'location' => 'สำนักงานใหญ่'],
            ['name' => 'UPS APC BX1100', 'type' => 'UPS', 'serial_number' => 'APC-UPS-006', 'status' => 'In Use', 'location' => 'สาขาบางนา'],
        ];
        foreach ($assetData as $data) {
            Asset::updateOrCreate([
                'serial_number' => $data['serial_number']
            ], $data);
        }

        // Seed 4 technician users (ชื่อจริง)
        $techUsers = [
            ['name' => 'สมชาย วิศวกร', 'username' => 'somchai', 'email' => 'somchai.tech@example.com'],
            ['name' => 'สมหญิง ช่างเทคนิค', 'username' => 'somying', 'email' => 'somying.tech@example.com'],
            ['name' => 'อนันต์ ช่างซ่อม', 'username' => 'anan', 'email' => 'anan.tech@example.com'],
            ['name' => 'วราภรณ์ IT Support', 'username' => 'waraporn', 'email' => 'waraporn.tech@example.com'],
        ];
        foreach ($techUsers as $user) {
            User::updateOrCreate([
                'email' => $user['email']
            ], [
                'name' => $user['name'],
                'username' => $user['username'],
                'password' => Hash::make('tech123'),
                'role' => 'Technician',
                'status' => 'Active',
            ]);
        }

        // Seed 8 incident managements (ชื่อจริง)
        $incidentData = [
            ['title' => 'คอมพิวเตอร์เปิดไม่ติด', 'description' => 'เครื่อง HP EliteBook ไม่สามารถเปิดได้', 'status' => 'Open', 'priority' => 'High', 'requester_id' => 1],
            ['title' => 'อินเทอร์เน็ตช้า', 'description' => 'สาขาบางนาใช้งานอินเทอร์เน็ตช้ามาก', 'status' => 'In Progress', 'priority' => 'Medium', 'requester_id' => 2],
            ['title' => 'ปริ้นเตอร์พิมพ์ไม่ออก', 'description' => 'Canon LBP6030 ไม่สามารถพิมพ์งาน', 'status' => 'Pending', 'priority' => 'Medium', 'requester_id' => 3],
            ['title' => 'จอมอนิเตอร์เสีย', 'description' => 'Samsung 24" ไม่แสดงผล', 'status' => 'Resolved', 'priority' => 'Low', 'requester_id' => 4],
            ['title' => 'โน้ตบุ๊คติดไวรัส', 'description' => 'Dell Inspiron พบมัลแวร์', 'status' => 'Open', 'priority' => 'Critical', 'requester_id' => 1],
            ['title' => 'Switch ไม่ทำงาน', 'description' => 'Cisco SG300 ไม่สามารถเชื่อมต่อเครือข่าย', 'status' => 'Closed', 'priority' => 'High', 'requester_id' => 2],
            ['title' => 'UPS แจ้งเตือนแบตเตอรี่', 'description' => 'APC BX1100 แจ้งเตือนเปลี่ยนแบต', 'status' => 'Open', 'priority' => 'Medium', 'requester_id' => 3],
            ['title' => 'ขอเพิ่มสิทธิ์ใช้งานระบบ', 'description' => 'ขอเพิ่มสิทธิ์ให้ user ใหม่', 'status' => 'Pending', 'priority' => 'Low', 'requester_id' => 4],
        ];
        foreach ($incidentData as $data) {
            Incident::updateOrCreate([
                'title' => $data['title']
            ], $data);
        }

        // Seed 4 branches (ชื่อจริง)
        $branchData = [
            ['code' => 'BR01', 'name' => 'สำนักงานใหญ่', 'province' => 'กรุงเทพมหานคร', 'status' => 'Active'],
            ['code' => 'BR02', 'name' => 'สาขาบางนา', 'province' => 'กรุงเทพมหานคร', 'status' => 'Active'],
            ['code' => 'BR03', 'name' => 'สาขารังสิต', 'province' => 'ปทุมธานี', 'status' => 'Active'],
            ['code' => 'BR04', 'name' => 'สาขาชลบุรี', 'province' => 'ชลบุรี', 'status' => 'Active'],
        ];
        foreach ($branchData as $data) {
            Branch::updateOrCreate([
                'code' => $data['code']
            ], $data);
        }

        // Seed 6 departments (ชื่อจริง)
        $deptData = [
            ['code' => 'DEPT01', 'name' => 'ฝ่ายไอที', 'status' => 'Active'],
            ['code' => 'DEPT02', 'name' => 'ฝ่ายบุคคล', 'status' => 'Active'],
            ['code' => 'DEPT03', 'name' => 'ฝ่ายบัญชี', 'status' => 'Active'],
            ['code' => 'DEPT04', 'name' => 'ฝ่ายจัดซื้อ', 'status' => 'Active'],
            ['code' => 'DEPT05', 'name' => 'ฝ่ายซ่อมบำรุง', 'status' => 'Active'],
            ['code' => 'DEPT06', 'name' => 'ฝ่ายบริการลูกค้า', 'status' => 'Active'],
        ];
        foreach ($deptData as $data) {
            Department::updateOrCreate([
                'code' => $data['code']
            ], $data);
        }
    }
}
