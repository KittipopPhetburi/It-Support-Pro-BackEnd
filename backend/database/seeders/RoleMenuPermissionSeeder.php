<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Role;
use App\Models\RoleMenuPermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleMenuPermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Seed base roles aligned with existing user role enums
            $roles = collect([
                ['name' => 'Admin', 'display_name' => 'Administrator'],
                ['name' => 'Technician', 'display_name' => 'Technician'],
                ['name' => 'Helpdesk', 'display_name' => 'Helpdesk'],
                ['name' => 'Purchase', 'display_name' => 'Purchase'],
                ['name' => 'User', 'display_name' => 'End User'],
            ])->mapWithKeys(function ($role) {
                $model = Role::firstOrCreate(['name' => $role['name']], ['display_name' => $role['display_name']]);
                return [$role['name'] => $model];
            });

            // Seed menus (aligned with UI groups)
            $menus = collect([
                // Service Management
                ['key' => 'dashboard_service', 'name' => 'Dashboard (Service)', 'group' => 'Service Management', 'sort_order' => 1],
                ['key' => 'incident_management', 'name' => 'Incident Management', 'group' => 'Service Management', 'sort_order' => 2],
                ['key' => 'service_catalog', 'name' => 'Service Catalog', 'group' => 'Service Management', 'sort_order' => 3],
                ['key' => 'problem_management', 'name' => 'Problem Management', 'group' => 'Service Management', 'sort_order' => 4],
                ['key' => 'business_hours_holidays', 'name' => 'Business Hours & Holidays', 'group' => 'Service Management', 'sort_order' => 5],

                // Equipment Management
                ['key' => 'dashboard_equipment', 'name' => 'Dashboard (Equipment)', 'group' => 'Equipment Management', 'sort_order' => 1],
                ['key' => 'asset_management', 'name' => 'Asset Management', 'group' => 'Equipment Management', 'sort_order' => 2],
                ['key' => 'asset', 'name' => 'Asset', 'group' => 'Equipment Management', 'sort_order' => 3],
                ['key' => 'asset_request', 'name' => 'Asset Request', 'group' => 'Equipment Management', 'sort_order' => 4],
                ['key' => 'other_request', 'name' => 'Other Request', 'group' => 'Equipment Management', 'sort_order' => 5],

                // Management
                ['key' => 'user_management', 'name' => 'User Management', 'group' => 'Management', 'sort_order' => 1],
                ['key' => 'sub_contract_management', 'name' => 'Sub-Contract Management', 'group' => 'Management', 'sort_order' => 2],
                ['key' => 'branch_management', 'name' => 'Branch Management', 'group' => 'Management', 'sort_order' => 3],
                ['key' => 'satisfaction_kpi', 'name' => 'Satisfaction & KPI', 'group' => 'Management', 'sort_order' => 4],
                ['key' => 'system_settings', 'name' => 'System Settings', 'group' => 'Management', 'sort_order' => 5],
            ])->mapWithKeys(function ($menu) {
                $model = Menu::firstOrCreate(['key' => $menu['key']], [
                    'name' => $menu['name'],
                    'group' => $menu['group'],
                    'sort_order' => $menu['sort_order'],
                ]);
                return [$menu['key'] => $model];
            });

            // Helper to apply presets
            $applyPreset = function ($role, array $preset) use ($menus) {
                foreach ($preset as $key => $abilities) {
                    if (!isset($menus[$key])) {
                        continue;
                    }
                    $menu = $menus[$key];
                    RoleMenuPermission::updateOrCreate(
                        ['role_id' => $role->id, 'menu_id' => $menu->id],
                        [
                            'can_view' => $abilities['view'] ?? false,
                            'can_create' => $abilities['create'] ?? false,
                            'can_update' => $abilities['update'] ?? false,
                            'can_delete' => $abilities['delete'] ?? false,
                        ]
                    );
                }
            };

            // Admin: full
            $applyPreset($roles['Admin'], $menus->mapWithKeys(fn($m) => [$m->key => ['view' => true, 'create' => true, 'update' => true, 'delete' => true]])->toArray());

            // Technician: เน้นงาน Incident/Problem, Asset maintenance (view/update), ไม่ยุ่ง config system
            if (isset($roles['Technician'])) {
                $applyPreset($roles['Technician'], [
                    'dashboard_service' => ['view' => true],
                    'incident_management' => ['view' => true, 'update' => true],
                    'service_catalog' => ['view' => true],
                    'problem_management' => ['view' => true, 'update' => true],
                    'business_hours_holidays' => [],
                    'dashboard_equipment' => ['view' => true],
                    'asset_management' => ['view' => true, 'update' => true],
                    'asset' => ['view' => true, 'update' => true],
                    'asset_request' => ['view' => true],
                    'other_request' => ['view' => true],
                    'user_management' => [],
                    'sub_contract_management' => [],
                    'branch_management' => [],
                    'satisfaction_kpi' => ['view' => true],
                    'system_settings' => [],
                ]);
            }

            // Helpdesk: รับแจ้ง/เปิดเคส สร้าง incident/requests ได้
            if (isset($roles['Helpdesk'])) {
                $applyPreset($roles['Helpdesk'], [
                    'dashboard_service' => ['view' => true],
                    'incident_management' => ['view' => true, 'create' => true],
                    'service_catalog' => ['view' => true],
                    'problem_management' => ['view' => true],
                    'business_hours_holidays' => [],
                    'dashboard_equipment' => ['view' => true],
                    'asset_management' => ['view' => true],
                    'asset' => ['view' => true],
                    'asset_request' => ['view' => true, 'create' => true],
                    'other_request' => ['view' => true, 'create' => true],
                    'user_management' => [],
                    'sub_contract_management' => [],
                    'branch_management' => [],
                    'satisfaction_kpi' => ['view' => true],
                    'system_settings' => [],
                ]);
            }

            // Purchase: เน้นงานจัดซื้อ/อนุมัติคำขอ
            if (isset($roles['Purchase'])) {
                $applyPreset($roles['Purchase'], [
                    'dashboard_equipment' => ['view' => true],
                    'asset_management' => ['view' => true, 'update' => true],
                    'asset' => ['view' => true],
                    'asset_request' => ['view' => true, 'update' => true],
                    'other_request' => ['view' => true, 'update' => true],
                    'dashboard_service' => ['view' => true],
                    'incident_management' => ['view' => true],
                    'service_catalog' => ['view' => true],
                    'problem_management' => ['view' => true],
                    'business_hours_holidays' => ['view' => true],
                    'user_management' => [],
                    'sub_contract_management' => ['view' => true],
                    'branch_management' => [],
                    'satisfaction_kpi' => ['view' => true],
                    'system_settings' => [],
                ]);
            }

            // User: ตามภาพตัวอย่าง (view dashboard, incident create, asset/other request create)
            if (isset($roles['User'])) {
                $applyPreset($roles['User'], [
                    'dashboard_service' => ['view' => true],
                    'incident_management' => ['view' => true, 'create' => true],
                    'service_catalog' => [],
                    'problem_management' => [],
                    'business_hours_holidays' => [],
                    'dashboard_equipment' => [],
                    'asset_management' => [],
                    'asset' => [],
                    'asset_request' => ['view' => true, 'create' => true],
                    'other_request' => ['view' => true, 'create' => true],
                    'user_management' => [],
                    'sub_contract_management' => [],
                    'branch_management' => [],
                    'satisfaction_kpi' => [],
                    'system_settings' => [],
                ]);
            }
        });
    }
}
