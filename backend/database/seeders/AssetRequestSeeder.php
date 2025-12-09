<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssetRequest;
use App\Models\User;
use App\Models\Asset;
use App\Models\Branch;
use App\Models\Department;

class AssetRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Get first user as requester
        $user = User::first();
        
        if (!$user) {
            $this->command->warn('No users found. Skipping AssetRequestSeeder.');
            return;
        }

        // Get first asset if exists
        $asset = Asset::first();
        
        // Get first branch if exists
        $branch = Branch::first();
        
        // Get first department if exists
        $department = Department::first();

        $requests = [
            [
                'requester_id' => $user->id,
                'requester_name' => $user->name,
                'request_type' => 'Requisition',
                'asset_type' => 'Hardware - Printer Canon LBP6030',
                'asset_id' => $asset ? $asset->id : null,
                'quantity' => 8,
                'justification' => 'ต้องการเพิ่มเครื่องปริ้นเตอร์สำหรับแผนกบัญชี',
                'reason' => 'เครื่องเก่าชำรุด ต้องการเครื่องใหม่',
                'status' => 'Approved',
                'request_date' => now(),
                'branch_id' => $branch ? $branch->id : null,
                'department_id' => $department ? $department->id : null,
                'department' => $department ? $department->name : 'แผนกไอที',
                'organization' => 'สำนักงานใหญ่',
                'approved_at' => now(),
                'approved_by' => 'Admin',
            ],
            [
                'requester_id' => $user->id,
                'requester_name' => $user->name,
                'request_type' => 'Borrow',
                'asset_type' => 'Hardware - Laptop Dell Latitude',
                'asset_id' => $asset ? $asset->id : null,
                'quantity' => 2,
                'justification' => 'ขอยืมเพื่อใช้ในการประชุมนอกสถานที่',
                'reason' => 'ประชุมลูกค้า 3 วัน',
                'status' => 'Pending',
                'request_date' => now()->subDays(1),
                'branch_id' => $branch ? $branch->id : null,
                'department_id' => $department ? $department->id : null,
                'department' => $department ? $department->name : 'แผนกขาย',
                'organization' => 'สาขากรุงเทพ',
            ],
            [
                'requester_id' => $user->id,
                'requester_name' => $user->name,
                'request_type' => 'Replace',
                'asset_type' => 'Hardware - Monitor 24 inch',
                'asset_id' => null,
                'quantity' => 1,
                'justification' => 'จอเก่าเสียหาย',
                'reason' => 'จอเดิมชำรุด ไม่สามารถใช้งานได้',
                'status' => 'Pending',
                'request_date' => now()->subDays(2),
                'branch_id' => $branch ? $branch->id : null,
                'department_id' => $department ? $department->id : null,
                'department' => $department ? $department->name : 'แผนกบุคคล',
                'organization' => 'สาขาเชียงใหม่',
            ],
        ];

        foreach ($requests as $request) {
            AssetRequest::create($request);
        }

        $this->command->info('Asset requests seeded successfully!');
    }
}
