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
        // Seed 15 assets with complete data (ข้อมูลจริงครบทุก field)
        $assetData = [
            // Hardware - Laptops
            [
                'name' => 'คอมพิวเตอร์ HP EliteBook',
                'category' => 'Hardware',
                'type' => 'Laptop',
                'brand' => 'HP',
                'model' => 'EliteBook 840 G8',
                'serial_number' => 'HP-ELITE-001',
                'inventory_number' => '641-60-0001',
                'status' => 'Available',
                'location' => 'สำนักงานใหญ่',
                'organization' => 'สำนักงานใหญ่',
                'department' => 'ฝ่ายไอที',
                'assigned_to' => null,
                'assigned_to_email' => null,
                'ip_address' => null,
                'mac_address' => null,
                'purchase_date' => '2023-06-15',
                'warranty_expiry' => '2026-06-15',
                'qr_code' => 'QR-HP-ELITE-001',
            ],
            [
                'name' => 'โน้ตบุ๊ค Dell Latitude',
                'category' => 'Hardware',
                'type' => 'Laptop',
                'brand' => 'Dell',
                'model' => 'Latitude 5520',
                'serial_number' => 'DELL-LAT-002',
                'inventory_number' => '641-60-0002',
                'status' => 'In Use',
                'location' => 'สาขาบางนา',
                'organization' => 'สาขาบางนา',
                'department' => 'ฝ่ายบัญชี',
                'assigned_to' => 'นายสมชาย ใจดี',
                'assigned_to_email' => 'somchai@company.com',
                'assigned_to_phone' => '081-234-5678',
                'ip_address' => '192.168.1.101',
                'mac_address' => '00:1A:2B:3C:4D:02',
                'purchase_date' => '2023-03-20',
                'warranty_expiry' => '2026-03-20',
                'qr_code' => 'QR-DELL-LAT-002',
            ],
            [
                'name' => 'โน้ตบุ๊ค Dell Inspiron',
                'category' => 'Hardware',
                'type' => 'Laptop',
                'brand' => 'Dell',
                'model' => 'Inspiron 15 7510',
                'serial_number' => 'DELL-INS-003',
                'inventory_number' => '641-60-0003',
                'status' => 'Maintenance',
                'location' => 'สาขารังสิต',
                'organization' => 'สาขารังสิต',
                'department' => 'ฝ่ายบุคคล',
                'assigned_to' => 'นางสาวสมหญิง รักงาน',
                'assigned_to_email' => 'somying@company.com',
                'ip_address' => null,
                'mac_address' => '00:1A:2B:3C:4D:03',
                'purchase_date' => '2022-11-10',
                'warranty_expiry' => '2025-11-10',
                'qr_code' => 'QR-DELL-INS-003',
            ],
            [
                'name' => 'MacBook Pro 14"',
                'category' => 'Hardware',
                'type' => 'Laptop',
                'brand' => 'Apple',
                'model' => 'MacBook Pro M3',
                'serial_number' => 'APPLE-MBP-004',
                'inventory_number' => '641-60-0004',
                'status' => 'In Use',
                'location' => 'สำนักงานใหญ่',
                'organization' => 'สำนักงานใหญ่',
                'department' => 'ฝ่ายกราฟิก',
                'assigned_to' => 'นายวิชัย ศิลปกร',
                'assigned_to_email' => 'wichai@company.com',
                'assigned_to_phone' => '089-111-2222',
                'ip_address' => '192.168.1.150',
                'mac_address' => 'AC:DE:48:00:11:22',
                'purchase_date' => '2024-01-05',
                'warranty_expiry' => '2027-01-05',
                'qr_code' => 'QR-APPLE-MBP-004',
            ],
            // Hardware - Desktops
            [
                'name' => 'เครื่องคอมพิวเตอร์ Lenovo ThinkCentre',
                'category' => 'Hardware',
                'type' => 'Desktop',
                'brand' => 'Lenovo',
                'model' => 'ThinkCentre M720q',
                'serial_number' => 'LENOVO-TC-005',
                'inventory_number' => '641-60-0005',
                'status' => 'Available',
                'location' => 'สาขาชลบุรี',
                'organization' => 'สาขาชลบุรี',
                'department' => 'ฝ่ายจัดซื้อ',
                'purchase_date' => '2023-08-01',
                'warranty_expiry' => '2026-08-01',
                'qr_code' => 'QR-LENOVO-TC-005',
            ],
            // Hardware - Printers
            [
                'name' => 'ปริ้นเตอร์ Canon LBP6030',
                'category' => 'Hardware',
                'type' => 'Printer',
                'brand' => 'Canon',
                'model' => 'LBP6030w',
                'serial_number' => 'CANON-PRT-006',
                'inventory_number' => '641-60-0006',
                'status' => 'In Use',
                'location' => 'สาขาบางนา',
                'organization' => 'สาขาบางนา',
                'department' => 'ฝ่ายบัญชี',
                'ip_address' => '192.168.1.200',
                'mac_address' => '00:00:48:11:22:33',
                'purchase_date' => '2022-05-20',
                'warranty_expiry' => '2025-05-20',
                'qr_code' => 'QR-CANON-PRT-006',
            ],
            [
                'name' => 'เครื่องพิมพ์ HP LaserJet Pro',
                'category' => 'Hardware',
                'type' => 'Printer',
                'brand' => 'HP',
                'model' => 'LaserJet Pro M404dn',
                'serial_number' => 'HP-LJ-007',
                'inventory_number' => '641-60-0007',
                'status' => 'Available',
                'location' => 'สำนักงานใหญ่',
                'organization' => 'สำนักงานใหญ่',
                'department' => 'ฝ่ายไอที',
                'ip_address' => '192.168.1.201',
                'purchase_date' => '2023-09-15',
                'warranty_expiry' => '2026-09-15',
                'qr_code' => 'QR-HP-LJ-007',
            ],
            // Hardware - Monitors
            [
                'name' => 'จอมอนิเตอร์ Samsung 24"',
                'category' => 'Hardware',
                'type' => 'Monitor',
                'brand' => 'Samsung',
                'model' => 'S24E450',
                'serial_number' => 'SAMSUNG-MON-008',
                'inventory_number' => '641-60-0008',
                'status' => 'Retired',
                'location' => 'สำนักงานใหญ่',
                'organization' => 'สำนักงานใหญ่',
                'department' => 'ฝ่ายไอที',
                'purchase_date' => '2019-01-10',
                'warranty_expiry' => '2022-01-10',
                'qr_code' => 'QR-SAMSUNG-MON-008',
            ],
            [
                'name' => 'จอมอนิเตอร์ LG UltraWide 34"',
                'category' => 'Hardware',
                'type' => 'Monitor',
                'brand' => 'LG',
                'model' => '34WN80C-B',
                'serial_number' => 'LG-MON-009',
                'inventory_number' => '641-60-0009',
                'status' => 'In Use',
                'location' => 'สำนักงานใหญ่',
                'organization' => 'สำนักงานใหญ่',
                'department' => 'ฝ่ายกราฟิก',
                'assigned_to' => 'นายวิชัย ศิลปกร',
                'purchase_date' => '2024-02-20',
                'warranty_expiry' => '2027-02-20',
                'qr_code' => 'QR-LG-MON-009',
            ],
            // Hardware - Network Equipment
            [
                'name' => 'Switch Cisco SG300',
                'category' => 'Hardware',
                'type' => 'Network Equipment',
                'brand' => 'Cisco',
                'model' => 'SG300-28PP',
                'serial_number' => 'CISCO-SW-010',
                'inventory_number' => '641-60-0010',
                'status' => 'In Use',
                'location' => 'สาขาชลบุรี',
                'organization' => 'สาขาชลบุรี',
                'department' => 'ฝ่ายไอที',
                'ip_address' => '192.168.10.1',
                'mac_address' => '00:1E:67:AA:BB:CC',
                'purchase_date' => '2021-07-01',
                'warranty_expiry' => '2024-07-01',
                'qr_code' => 'QR-CISCO-SW-010',
            ],
            // Hardware - UPS
            [
                'name' => 'UPS APC BX1100',
                'category' => 'Hardware',
                'type' => 'UPS',
                'brand' => 'APC',
                'model' => 'Back-UPS BX1100C',
                'serial_number' => 'APC-UPS-011',
                'inventory_number' => '641-60-0011',
                'status' => 'In Use',
                'location' => 'สาขาบางนา',
                'organization' => 'สาขาบางนา',
                'department' => 'ฝ่ายไอที',
                'purchase_date' => '2022-04-15',
                'warranty_expiry' => '2025-04-15',
                'qr_code' => 'QR-APC-UPS-011',
            ],
            // Software - Licenses
            [
                'name' => 'Microsoft Office 365 Business',
                'category' => 'Software',
                'type' => 'License',
                'brand' => 'Microsoft',
                'model' => 'Office 365 Business Premium',
                'serial_number' => 'MS-O365-012',
                'inventory_number' => '641-70-0001',
                'status' => 'In Use',
                'organization' => 'สำนักงานใหญ่',
                'department' => 'ฝ่ายไอที',
                'license_key' => 'XXXXX-XXXXX-XXXXX-XXXXX-O365B',
                'license_type' => 'Subscription',
                'total_licenses' => 50,
                'used_licenses' => 42,
                'start_date' => '2024-01-01',
                'expiry_date' => '2024-12-31',
                'qr_code' => 'QR-MS-O365-012',
            ],
            [
                'name' => 'Adobe Creative Cloud',
                'category' => 'Software',
                'type' => 'License',
                'brand' => 'Adobe',
                'model' => 'Creative Cloud All Apps',
                'serial_number' => 'ADOBE-CC-013',
                'inventory_number' => '641-70-0002',
                'status' => 'In Use',
                'organization' => 'สำนักงานใหญ่',
                'department' => 'ฝ่ายกราฟิก',
                'license_key' => 'XXXXX-XXXXX-XXXXX-XXXXX-ADOBE',
                'license_type' => 'Subscription',
                'total_licenses' => 10,
                'used_licenses' => 8,
                'start_date' => '2024-03-01',
                'expiry_date' => '2025-02-28',
                'qr_code' => 'QR-ADOBE-CC-013',
            ],
            [
                'name' => 'Windows 11 Pro',
                'category' => 'Software',
                'type' => 'License',
                'brand' => 'Microsoft',
                'model' => 'Windows 11 Pro Volume License',
                'serial_number' => 'MS-WIN11-014',
                'inventory_number' => '641-70-0003',
                'status' => 'In Use',
                'organization' => 'สำนักงานใหญ่',
                'department' => 'ฝ่ายไอที',
                'license_key' => 'XXXXX-XXXXX-XXXXX-XXXXX-WIN11',
                'license_type' => 'Perpetual',
                'total_licenses' => 100,
                'used_licenses' => 85,
                'start_date' => '2023-06-01',
                'qr_code' => 'QR-MS-WIN11-014',
            ],
            [
                'name' => 'Kaspersky Endpoint Security',
                'category' => 'Software',
                'type' => 'License',
                'brand' => 'Kaspersky',
                'model' => 'Endpoint Security Cloud Plus',
                'serial_number' => 'KASP-SEC-015',
                'inventory_number' => '641-70-0004',
                'status' => 'In Use',
                'organization' => 'สำนักงานใหญ่',
                'department' => 'ฝ่ายไอที',
                'license_key' => 'XXXXX-XXXXX-XXXXX-XXXXX-KASP',
                'license_type' => 'Subscription',
                'total_licenses' => 100,
                'used_licenses' => 78,
                'start_date' => '2024-06-01',
                'expiry_date' => '2025-05-31',
                'qr_code' => 'QR-KASP-SEC-015',
            ],
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
