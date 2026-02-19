<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Role;
use App\Models\RoleMenuPermission;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // 1. Define Menus
        $menus = [
            // Equipment Management
            ['key' => 'dashboard_equipment', 'name' => 'Dashboard (Equipment)', 'group' => 'Equipment Management', 'sort_order' => 1],
            ['key' => 'assets', 'name' => 'Asset', 'group' => 'Equipment Management', 'sort_order' => 2],
            ['key' => 'asset_management', 'name' => 'Asset Management', 'group' => 'Equipment Management', 'sort_order' => 3],
            ['key' => 'asset_requests', 'name' => 'Asset Request', 'group' => 'Equipment Management', 'sort_order' => 4],
            ['key' => 'other_requests', 'name' => 'Other Request', 'group' => 'Equipment Management', 'sort_order' => 5],
            ['key' => 'return_assets', 'name' => 'Return Assets', 'group' => 'assets', 'sort_order' => 6], // Group matches screenshot
            
            // Management
            ['key' => 'users', 'name' => 'User', 'group' => 'Management', 'sort_order' => 10],
            ['key' => 'roles', 'name' => 'Manage Role', 'group' => 'Management', 'sort_order' => 11],
            ['key' => 'system_settings', 'name' => 'System Settings', 'group' => 'Management', 'sort_order' => 12],
            
            // Service (Added for completeness based on likely features, can be disabled if not used)
            ['key' => 'incidents', 'name' => 'Incident', 'group' => 'Service', 'sort_order' => 20],
        ];

        foreach ($menus as $menuData) {
            Menu::updateOrCreate(
                ['key' => $menuData['key']],
                $menuData
            );
        }
        $this->command?->info('Menus seeded successfully.');

        // 2. Define Default Permissions per Role
        // Admin gets everything
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $allMenus = Menu::all();
            foreach ($allMenus as $menu) {
                RoleMenuPermission::updateOrCreate(
                    [
                        'role_id' => $adminRole->id,
                        'menu_id' => $menu->id,
                    ],
                    [
                        'role_name' => $adminRole->name,
                        'menu_name' => $menu->name,
                        'can_view' => true,
                        'can_create' => true,
                        'can_update' => true,
                        'can_delete' => true,
                    ]
                );
            }
            $this->command?->info('Admin permissions granted.');
        }

        // Helpdesk / Technician (View & Manage Incidents/Assets but maybe strict on Settings)
        $techRoles = Role::whereIn('name', ['Technician', 'Helpdesk'])->get();
        foreach ($techRoles as $role) {
            $techMenus = Menu::whereIn('key', ['dashboard_equipment', 'assets', 'asset_requests', 'return_assets', 'incidents'])->get();
            foreach ($techMenus as $menu) {
                 RoleMenuPermission::updateOrCreate(
                    [
                        'role_id' => $role->id,
                        'menu_id' => $menu->id,
                    ],
                    [
                        'role_name' => $role->name,
                        'menu_name' => $menu->name,
                        'can_view' => true,
                        'can_create' => true, // Can create tickets/assets
                        'can_update' => true, // Can update status
                        'can_delete' => false, // Cannot delete
                    ]
                );
            }
        }
        
        // Purchase (Assets & Requests)
        $purchaseRole = Role::where('name', 'Purchase')->first();
        if ($purchaseRole) {
             $purchaseMenus = Menu::whereIn('key', ['dashboard_equipment', 'assets', 'asset_management', 'asset_requests'])->get();
             foreach ($purchaseMenus as $menu) {
                 RoleMenuPermission::updateOrCreate(
                    [
                        'role_id' => $purchaseRole->id,
                        'menu_id' => $menu->id,
                    ],
                    [
                        'role_name' => $purchaseRole->name,
                        'menu_name' => $menu->name,
                        'can_view' => true,
                        'can_create' => true,
                        'can_update' => true,
                        'can_delete' => false,
                    ]
                );
            }
        }

        $this->command?->info('Role permissions seeded successfully.');
    }
}
