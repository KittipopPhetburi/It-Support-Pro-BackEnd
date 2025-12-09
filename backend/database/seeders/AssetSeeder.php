<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\Branch;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();
        
        $assets = [
            // Laptops
            ['inventory_number' => 'IT-LAP-001', 'name' => 'Dell Latitude 5520', 'category' => 'Hardware', 'type' => 'Laptop', 'brand' => 'Dell', 'model' => 'Latitude 5520', 'serial_number' => 'DL5520-001', 'status' => 'In Use', 'purchase_date' => now()->subYears(1), 'warranty_expiry' => now()->addYears(2), 'location' => 'IT Department', 'branch_id' => $branch?->id],
            ['inventory_number' => 'IT-LAP-002', 'name' => 'HP EliteBook 840', 'category' => 'Hardware', 'type' => 'Laptop', 'brand' => 'HP', 'model' => 'EliteBook 840 G8', 'serial_number' => 'HP840-002', 'status' => 'In Use', 'purchase_date' => now()->subMonths(8), 'warranty_expiry' => now()->addYears(2), 'location' => 'HR Department', 'branch_id' => $branch?->id],
            ['inventory_number' => 'IT-LAP-003', 'name' => 'Lenovo ThinkPad X1', 'category' => 'Hardware', 'type' => 'Laptop', 'brand' => 'Lenovo', 'model' => 'ThinkPad X1 Carbon', 'serial_number' => 'LN-X1-003', 'status' => 'In Use', 'purchase_date' => now()->subMonths(6), 'warranty_expiry' => now()->addYears(2), 'location' => 'Finance', 'branch_id' => $branch?->id],
            ['inventory_number' => 'IT-LAP-004', 'name' => 'MacBook Pro 14"', 'category' => 'Hardware', 'type' => 'Laptop', 'brand' => 'Apple', 'model' => 'MacBook Pro 14" M1', 'serial_number' => 'MBP14-004', 'status' => 'In Use', 'purchase_date' => now()->subMonths(4), 'warranty_expiry' => now()->addYears(2), 'location' => 'Marketing', 'branch_id' => $branch?->id],
            ['inventory_number' => 'IT-LAP-005', 'name' => 'Asus ZenBook 14', 'category' => 'Hardware', 'type' => 'Laptop', 'brand' => 'Asus', 'model' => 'ZenBook 14', 'serial_number' => 'AS-ZB14-005', 'status' => 'Maintenance', 'purchase_date' => now()->subYears(2), 'warranty_expiry' => now()->subMonths(1), 'location' => 'IT Department', 'branch_id' => $branch?->id],

            // Desktops
            ['inventory_number' => 'IT-PC-001', 'name' => 'Dell OptiPlex 7090', 'category' => 'Hardware', 'type' => 'Desktop', 'brand' => 'Dell', 'model' => 'OptiPlex 7090', 'serial_number' => 'DL7090-001', 'status' => 'In Use', 'purchase_date' => now()->subYears(1), 'warranty_expiry' => now()->addYears(2), 'location' => 'Accounting', 'branch_id' => $branch?->id],
            ['inventory_number' => 'IT-PC-002', 'name' => 'HP ProDesk 600', 'category' => 'Hardware', 'type' => 'Desktop', 'brand' => 'HP', 'model' => 'ProDesk 600 G6', 'serial_number' => 'HP600-002', 'status' => 'In Use', 'purchase_date' => now()->subMonths(10), 'warranty_expiry' => now()->addYears(2), 'location' => 'Sales', 'branch_id' => $branch?->id],

            // Printers
            ['inventory_number' => 'IT-PRT-001', 'name' => 'HP LaserJet Pro M404dn', 'category' => 'Hardware', 'type' => 'Printer', 'brand' => 'HP', 'model' => 'LaserJet Pro M404dn', 'serial_number' => 'HP404-001', 'status' => 'In Use', 'purchase_date' => now()->subYears(1), 'warranty_expiry' => now()->addYears(1), 'location' => 'IT Department', 'branch_id' => $branch?->id],
            ['inventory_number' => 'IT-PRT-002', 'name' => 'Canon LBP6030', 'category' => 'Hardware', 'type' => 'Printer', 'brand' => 'Canon', 'model' => 'LBP6030', 'serial_number' => 'CN6030-002', 'status' => 'Available', 'purchase_date' => now()->subMonths(8), 'warranty_expiry' => now()->addMonths(4), 'location' => 'HR Department', 'branch_id' => $branch?->id],
            ['inventory_number' => 'IT-PRT-003', 'name' => 'Epson L3210', 'category' => 'Hardware', 'type' => 'Printer', 'brand' => 'Epson', 'model' => 'L3210', 'serial_number' => 'EP3210-003', 'status' => 'Available', 'purchase_date' => now()->subMonths(5), 'warranty_expiry' => now()->addMonths(7), 'location' => 'Finance', 'branch_id' => $branch?->id],

            // Monitors
            ['inventory_number' => 'IT-MON-001', 'name' => 'Dell P2422H 24"', 'category' => 'Hardware', 'type' => 'Monitor', 'brand' => 'Dell', 'model' => 'P2422H', 'serial_number' => 'DL2422-001', 'status' => 'Available', 'purchase_date' => now()->subYears(1), 'warranty_expiry' => now()->addYears(2), 'location' => 'IT Department', 'branch_id' => $branch?->id],
            ['inventory_number' => 'IT-MON-002', 'name' => 'LG 27UL500 27"', 'category' => 'Hardware', 'type' => 'Monitor', 'brand' => 'LG', 'model' => '27UL500', 'serial_number' => 'LG27U-002', 'status' => 'In Use', 'purchase_date' => now()->subMonths(9), 'warranty_expiry' => now()->addYears(2), 'location' => 'Marketing', 'branch_id' => $branch?->id],
            ['inventory_number' => 'IT-MON-003', 'name' => 'Samsung 24" LED', 'category' => 'Hardware', 'type' => 'Monitor', 'brand' => 'Samsung', 'model' => 'S24R350', 'serial_number' => 'SM24R-003', 'status' => 'Retired', 'purchase_date' => now()->subYears(4), 'warranty_expiry' => now()->subYears(1), 'location' => 'Storage', 'branch_id' => $branch?->id],

            // Network Equipment
            ['inventory_number' => 'IT-NET-001', 'name' => 'Cisco Switch 24-port', 'category' => 'Network', 'type' => 'Switch', 'brand' => 'Cisco', 'model' => 'Catalyst 2960', 'serial_number' => 'CS2960-001', 'status' => 'In Use', 'purchase_date' => now()->subYears(2), 'warranty_expiry' => now()->addYears(1), 'location' => 'Server Room', 'branch_id' => $branch?->id],
            ['inventory_number' => 'IT-NET-002', 'name' => 'TP-Link Router', 'category' => 'Network', 'type' => 'Router', 'brand' => 'TP-Link', 'model' => 'Archer AX6000', 'serial_number' => 'TP6000-002', 'status' => 'In Use', 'purchase_date' => now()->subYears(1), 'warranty_expiry' => now()->addYears(2), 'location' => 'Server Room', 'branch_id' => $branch?->id],

            // Servers
            ['inventory_number' => 'IT-SRV-001', 'name' => 'Dell PowerEdge R740', 'category' => 'Server', 'type' => 'Physical Server', 'brand' => 'Dell', 'model' => 'PowerEdge R740', 'serial_number' => 'DLR740-001', 'status' => 'In Use', 'purchase_date' => now()->subYears(2), 'warranty_expiry' => now()->addYears(3), 'location' => 'Data Center', 'branch_id' => $branch?->id],
            ['inventory_number' => 'IT-SRV-002', 'name' => 'HP ProLiant DL380', 'category' => 'Server', 'type' => 'Physical Server', 'brand' => 'HP', 'model' => 'ProLiant DL380 Gen10', 'serial_number' => 'HP380-002', 'status' => 'In Use', 'purchase_date' => now()->subYears(1), 'warranty_expiry' => now()->addYears(4), 'location' => 'Data Center', 'branch_id' => $branch?->id],

            // Mobile Devices
            ['inventory_number' => 'IT-MOB-001', 'name' => 'iPhone 13 Pro', 'category' => 'Mobile', 'type' => 'Smartphone', 'brand' => 'Apple', 'model' => 'iPhone 13 Pro', 'serial_number' => 'IP13P-001', 'status' => 'In Use', 'purchase_date' => now()->subMonths(11), 'warranty_expiry' => now()->addMonths(1), 'location' => 'Sales Manager', 'branch_id' => $branch?->id],
            ['inventory_number' => 'IT-MOB-002', 'name' => 'Samsung Galaxy S22', 'category' => 'Mobile', 'type' => 'Smartphone', 'brand' => 'Samsung', 'model' => 'Galaxy S22', 'serial_number' => 'SMS22-002', 'status' => 'In Use', 'purchase_date' => now()->subMonths(7), 'warranty_expiry' => now()->addMonths(5), 'location' => 'Marketing Manager', 'branch_id' => $branch?->id],

            // Accessories
            ['inventory_number' => 'IT-KEY-001', 'name' => 'Logitech MX Keys', 'category' => 'Accessories', 'type' => 'Keyboard', 'brand' => 'Logitech', 'model' => 'MX Keys', 'serial_number' => 'LGMXK-001', 'status' => 'Available', 'purchase_date' => now()->subMonths(6), 'warranty_expiry' => now()->addYears(1), 'location' => 'IT Department', 'branch_id' => $branch?->id],
            ['inventory_number' => 'IT-MOU-001', 'name' => 'Logitech MX Master 3', 'category' => 'Accessories', 'type' => 'Mouse', 'brand' => 'Logitech', 'model' => 'MX Master 3', 'serial_number' => 'LGMXM-001', 'status' => 'Available', 'purchase_date' => now()->subMonths(6), 'warranty_expiry' => now()->addYears(1), 'location' => 'IT Department', 'branch_id' => $branch?->id],
        ];

        foreach ($assets as $asset) {
            Asset::updateOrCreate(
                ['inventory_number' => $asset['inventory_number']],
                $asset
            );
        }

        $this->command->info('Assets seeded successfully!');
    }
}
