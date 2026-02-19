<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Asset;
use App\Models\Incident;
use App\Models\AssetRequest;
use App\Models\OtherRequest;
use App\Models\BorrowingHistory;
use App\Models\MaintenanceHistory;
use App\Models\User;
use Carbon\Carbon;

class RefreshOperationalDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Disable Foreign Keys & Truncate Tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $this->command->info('Cleaning operational tables...');
        
        Incident::truncate();
        Asset::truncate();
        AssetRequest::truncate();
        OtherRequest::truncate();
        BorrowingHistory::truncate();
        MaintenanceHistory::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Get Reference Data (Users, Branch)
        $admin = User::where('role', 'Admin')->first() ?? User::first();
        $user = User::where('role', 'User')->first() ?? $admin;
        $branchId = $admin->branch_id ?? 1;

        $this->command->info('Seeding new assets with correct logic...');

        // ---------------------------------------------------------
        // Scenario 1: Multi-Serial Asset (All Available)
        // ---------------------------------------------------------
        Asset::create([
            'name' => 'HP EliteBook 840 G8',
            'type' => 'Asset',
            'category' => 'Hardware',
            'brand' => 'HP',
            'model' => 'EliteBook 840 G8',
            'serial_number' => "HP840-001\nHP840-002\nHP840-003",
            'quantity' => 3,
            'status' => 'Available', // Master status
            // 'cost' => 35000, // Column not in DB
            'branch_id' => $branchId,
            'purchase_date' => Carbon::now()->subMonths(6),
            'warranty_expiry' => Carbon::now()->addMonths(18),
            'location' => 'IT Stock Room',
            // 'description' => 'Standard issue laptop for staff',
        ]);

        // ---------------------------------------------------------
        // Scenario 2: Single-Serial Asset (Available)
        // ---------------------------------------------------------
        Asset::create([
            'name' => 'Epson Projector EB-X06',
            'type' => 'Asset',
            'category' => 'Hardware',
            'brand' => 'Epson',
            'model' => 'EB-X06',
            'serial_number' => "EPS-PROJ-001",
            'quantity' => 1,
            'status' => 'Available',
            // 'cost' => 12900,
            'branch_id' => $branchId,
            'purchase_date' => Carbon::now()->subMonths(2),
            'location' => 'Meeting Room 1',
        ]);

        // ---------------------------------------------------------
        // Scenario 3: Asset Under Maintenance (Sync Test)
        // ---------------------------------------------------------
        $printer = Asset::create([
            'name' => 'Canon Pixma G3010',
            'type' => 'Asset',
            'category' => 'Hardware',
            'brand' => 'Canon',
            'model' => 'G3010',
            'serial_number' => "CN-PIX-999",
            'quantity' => 1,
            'status' => 'Maintenance', // Correct Master Status for Single Unit
            // 'cost' => 4500,
            'branch_id' => $branchId,
            'location' => 'HR Department',
            'serial_mapping' => [
                'CN-PIX-999' => [
                    'status' => 'Maintenance',
                    'note' => 'Incident #1: กระดาษติดบ่อย',
                    'updated_at' => Carbon::now()->toDateTimeString()
                ]
            ]
        ]);

        // Create the Incident linking to this asset
        Incident::create([
            'title' => 'แจ้งซ่อม: Canon Pixma G3010',
            'description' => 'กระดาษติดบ่อยมาก พิมพ์ไม่ออก',
            'category' => 'Hardware',
            'subcategory' => 'แจ้งซ่อม',
            'priority' => 'Medium',
            'status' => 'Open',
            'asset_id' => $printer->id,
            'asset_name' => $printer->name,
            'asset_brand' => $printer->brand,
            'asset_model' => $printer->model,
            'asset_serial_number' => 'CN-PIX-999',
            'requester_id' => $user->id,
            'branch_id' => $branchId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'previous_asset_status' => 'Available', // For correct restoration
        ]);

        // ---------------------------------------------------------
        // Scenario 4: Consumable (Mouse) - No Serials
        // ---------------------------------------------------------
        Asset::create([
            'name' => 'Logitech Wireless Mouse M185',
            'type' => 'Consumable',
            'category' => 'Hardware',
            'brand' => 'Logitech',
            'model' => 'M185',
            'quantity' => 20,
            'status' => 'Available',
            // 'cost' => 450,
            'branch_id' => $branchId,
            'location' => 'IT Cabinet',
        ]);
        
        // ---------------------------------------------------------
        // Scenario 5: Multi-Serial with 1 Broken (Partial Maintenance)
        // ---------------------------------------------------------
        $monitor = Asset::create([
            'name' => 'Dell Monitor 24"',
            'type' => 'Asset',
            'category' => 'Hardware',
            'brand' => 'Dell',
            'model' => 'S2421H',
            'serial_number' => "DL-MON-001\nDL-MON-002\nDL-MON-003",
            'quantity' => 3,
            'status' => 'Available', // Master status remains Available
            // 'cost' => 5900,
            'branch_id' => $branchId,
            'location' => 'Design Team',
            'serial_mapping' => [
                'DL-MON-002' => [
                    'status' => 'Maintenance',
                    'note' => 'Incident #2: จอเป็นเส้น',
                    'updated_at' => Carbon::now()->toDateTimeString()
                ]
            ]
        ]);

        Incident::create([
            'title' => 'จอภาพเป็นเส้น',
            'description' => 'จอ Monitor ตัวกลางมีเส้นสีเขียวขึ้น',
            'category' => 'Hardware',
            'priority' => 'Low',
            'status' => 'In Progress',
            'repair_status' => 'In Progress',
            'asset_id' => $monitor->id,
            'asset_name' => $monitor->name,
            'asset_serial_number' => 'DL-MON-002',
            'requester_id' => $user->id,
            'branch_id' => $branchId,
            'previous_asset_status' => 'Available',
        ]);

        // ---------------------------------------------------------
        // Scenario 6: Software (Subscription)
        // ---------------------------------------------------------
        Asset::create([
            'name' => 'Adobe Creative Cloud All Apps',
            'type' => 'License', // Or Asset type 'Software'
            'category' => 'Software',
            'brand' => 'Adobe',
            'model' => 'CC 2024',
            'serial_number' => "KEY-ADOBE-001\nKEY-ADOBE-002\nKEY-ADOBE-003\nKEY-ADOBE-004\nKEY-ADOBE-005",
            'quantity' => 5, // Based on number of keys
            'total_licenses' => 5,
            'used_licenses' => 0,
            'status' => 'Available',
            'license_type' => 'Subscription',
            'purchase_date' => Carbon::now()->subMonths(1),
            'expiry_date' => Carbon::now()->addMonths(11),
            'branch_id' => $branchId,
            // 'description' => 'Design team licenses',
        ]);

        // ---------------------------------------------------------
        // Scenario 7: Software (Perpetual - Windows)
        // ---------------------------------------------------------
        Asset::create([
            'name' => 'Windows 11 Pro',
            'type' => 'License',
            'category' => 'Software',
            'brand' => 'Microsoft',
            'model' => 'Pro',
            'serial_number' => "WIN11-KEY-001\nWIN11-KEY-002\nWIN11-KEY-003",
            'quantity' => 3,
            'total_licenses' => 3,
            'used_licenses' => 0,
            'status' => 'Available',
            'license_type' => 'Perpetual',
            'purchase_date' => Carbon::now()->subMonths(12),
            'branch_id' => $branchId,
            'location' => 'Digital Licenses',
        ]);
        
        $this->command?->info('Data refresh completed successfully!');
    }
}
