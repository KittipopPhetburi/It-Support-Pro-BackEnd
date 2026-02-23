<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Menu;
use App\Models\Role;
use App\Models\RoleMenuPermission;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing menus that the frontend sidebar references
        // but were not in the original PermissionSeeder
        $missingMenus = [
            // Service Management group
            ['key' => 'dashboard_service', 'name' => 'Dashboard (Service)', 'group' => 'Service', 'sort_order' => 15],
            ['key' => 'service_catalog', 'name' => 'Service Catalog', 'group' => 'Service', 'sort_order' => 21],
            ['key' => 'problem_management', 'name' => 'Problem Management', 'group' => 'Service', 'sort_order' => 22],
            ['key' => 'satisfaction_kpi', 'name' => 'Satisfaction & KPI', 'group' => 'Service', 'sort_order' => 23],
            ['key' => 'business_hours_holidays', 'name' => 'Business Hours & Holidays', 'group' => 'Service', 'sort_order' => 24],
            ['key' => 'knowledge_base', 'name' => 'Knowledge Base', 'group' => 'Service', 'sort_order' => 25],

            // Management group
            ['key' => 'branch_management', 'name' => 'Branch Management', 'group' => 'Management', 'sort_order' => 13],
            ['key' => 'sub_contract_management', 'name' => 'Sub-Contract Management', 'group' => 'Management', 'sort_order' => 14],
            ['key' => 'activity_log', 'name' => 'Activity Log', 'group' => 'Management', 'sort_order' => 15],
        ];

        foreach ($missingMenus as $menuData) {
            // Only insert if the key doesn't already exist
            if (!DB::table('menus')->where('key', $menuData['key'])->exists()) {
                $menuId = DB::table('menus')->insertGetId(array_merge($menuData, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));

                // Give Admin full permissions for each new menu
                $adminRole = DB::table('roles')->where('name', 'Admin')->first();
                if ($adminRole) {
                    DB::table('role_menu_permissions')->insert([
                        'role_id' => $adminRole->id,
                        'menu_id' => $menuId,
                        'role_name' => 'Admin',
                        'menu_name' => $menuData['name'],
                        'can_view' => true,
                        'can_create' => true,
                        'can_update' => true,
                        'can_delete' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $keys = [
            'dashboard_service', 'service_catalog', 'problem_management',
            'satisfaction_kpi', 'business_hours_holidays', 'knowledge_base',
            'branch_management', 'sub_contract_management', 'activity_log',
        ];

        foreach ($keys as $key) {
            $menu = DB::table('menus')->where('key', $key)->first();
            if ($menu) {
                DB::table('role_menu_permissions')->where('menu_id', $menu->id)->delete();
                DB::table('user_menu_permissions')->where('menu_id', $menu->id)->delete();
                DB::table('menus')->where('id', $menu->id)->delete();
            }
        }
    }
};
