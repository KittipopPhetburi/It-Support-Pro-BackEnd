<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Role;
use App\Models\RoleMenuPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    /**
     * GET /api/roles/{roleId}/permissions
     */
    public function index(int $roleId)
    {
        $role = Role::findOrFail($roleId);

        // Load all menus to ensure we return every menu with defaults
        $menus = Menu::orderBy('group')->orderBy('sort_order')->get();
        $permissions = RoleMenuPermission::where('role_id', $role->id)->get()->keyBy('menu_id');

        $result = $menus->map(function (Menu $menu) use ($permissions) {
            $perm = $permissions->get($menu->id);
            return [
                'menu_id' => $menu->id,
                'menu_key' => $menu->key,
                'menu_name' => $menu->name,
                'menu_group' => $menu->group,
                'can_view' => $perm?->can_view ?? false,
                'can_create' => $perm?->can_create ?? false,
                'can_update' => $perm?->can_update ?? false,
                'can_delete' => $perm?->can_delete ?? false,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'role' => $role,
                'permissions' => $result,
            ],
        ]);
    }

    /**
     * PUT /api/roles/{roleId}/permissions
     */
    public function update(Request $request, int $roleId)
    {
        $role = Role::findOrFail($roleId);

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*.menu_id' => 'required|exists:menus,id',
            'permissions.*.can_view' => 'boolean',
            'permissions.*.can_create' => 'boolean',
            'permissions.*.can_update' => 'boolean',
            'permissions.*.can_delete' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $role, $request) {
            // Get authenticated user's username
            $username = $request->user()?->name ?? 'System';
            
            foreach ($validated['permissions'] as $item) {
                // Get menu name for better readability
                $menu = Menu::find($item['menu_id']);
                
                RoleMenuPermission::updateOrCreate(
                    [
                        'role_id' => $role->id,
                        'menu_id' => $item['menu_id'],
                    ],
                    [
                        'role_name' => $role->display_name ?? $role->name,
                        'menu_name' => $menu?->name,
                        'can_view' => $item['can_view'] ?? false,
                        'can_create' => $item['can_create'] ?? false,
                        'can_update' => $item['can_update'] ?? false,
                        'can_delete' => $item['can_delete'] ?? false,
                        'created_by_username' => $username,
                    ]
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'บันทึกสิทธิ์สำเร็จ',
        ]);
    }

    /**
     * POST /api/roles/{roleId}/permissions/reset-default
     * Reset permissions to default values from seeder
     */
    public function resetToDefault(int $roleId)
    {
        $role = Role::findOrFail($roleId);
        
        // Run seeder logic for this specific role
        DB::transaction(function () use ($role) {
            // Delete existing permissions
            RoleMenuPermission::where('role_id', $role->id)->delete();
            
            // Re-seed with default permissions
            $this->seedDefaultPermissions($role);
        });

        return response()->json([
            'success' => true,
            'message' => 'รีเซ็ตสิทธิ์เป็นค่าเริ่มต้นสำเร็จ',
        ]);
    }

    /**
     * Seed default permissions based on role name
     */
    private function seedDefaultPermissions(Role $role)
    {
        $menus = Menu::all()->keyBy('key');
        
        $applyPreset = function ($preset) use ($role, $menus) {
            foreach ($preset as $key => $abilities) {
                if (!isset($menus[$key])) continue;
                
                $menu = $menus[$key];
                RoleMenuPermission::create([
                    'role_id' => $role->id,
                    'menu_id' => $menu->id,
                    'role_name' => $role->display_name ?? $role->name,
                    'menu_name' => $menu->name,
                    'can_view' => $abilities['view'] ?? false,
                    'can_create' => $abilities['create'] ?? false,
                    'can_update' => $abilities['update'] ?? false,
                    'can_delete' => $abilities['delete'] ?? false,
                    'created_by_username' => 'System',
                ]);
            }
        };

        // Default presets based on role name
        switch ($role->name) {
            case 'Admin':
                // 5. Admin - จัดการทุกอย่างได้เต็มรูปแบบ
                $applyPreset($menus->mapWithKeys(fn($m) => [$m->key => [
                    'view' => true, 'create' => true, 'update' => true, 'delete' => true
                ]])->toArray());
                break;

            case 'Technician':
                // 3. Technician - จัดการเหตุการณ์, ปัญหา, ฐานความรู้, อนุมัติคำขอ, Export
                $applyPreset([
                    'dashboard_service' => ['view' => true],
                    'dashboard_equipment' => ['view' => true],
                    'incident_management' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true],
                    'service_catalog' => ['view' => true],
                    'problem_management' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true],
                    'knowledge_base' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true],
                    'asset_request' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true],
                    'other_request' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true],
                    'satisfaction_kpi' => ['view' => true],
                ]);
                break;

            case 'Helpdesk':
                // 4. Helpdesk - จัดการเหตุการณ์, แค็ตตาล็อก, ปัญหา, ฐานความรู้, ดูคำขอ, ติดต่อช่างภายนอก, Export
                $applyPreset([
                    'dashboard_service' => ['view' => true],
                    'incident_management' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true],
                    'service_catalog' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true],
                    'problem_management' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true],
                    'knowledge_base' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true],
                    'asset_request' => ['view' => true],
                    'other_request' => ['view' => true],
                    'satisfaction_kpi' => ['view' => true],
                    'branch_management' => ['view' => true],
                    'sub_contract_management' => ['view' => true, 'create' => true, 'update' => true],
                ]);
                break;

            case 'Purchase':
                // 2. Purchase - จัดการอุปกรณ์และคำขอเบิก/ยืม/ทดแทน ดูหน่วยงาน
                $applyPreset([
                    'dashboard_equipment' => ['view' => true],
                    'asset_management' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true],
                    'asset' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true],
                    'asset_request' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true],
                    'other_request' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true],
                    'branch_management' => ['view' => true],
                ]);
                break;

            case 'User':
                // 1. User - เพิ่ม/แก้ไขเหตุการณ์ของตัวเอง, ดูแค็ตตาล็อก, ทำคำขอยืม/เบิก, ดูฐานความรู้, ประเมินความพึงพอใจ
                $applyPreset([
                    'dashboard_service' => ['view' => true],
                    'incident_management' => ['view' => true, 'create' => true, 'update' => true],
                    'service_catalog' => ['view' => true],
                    'knowledge_base' => ['view' => true],
                    'asset_request' => ['view' => true, 'create' => true, 'update' => true],
                    'other_request' => ['view' => true, 'create' => true, 'update' => true],
                    'satisfaction_kpi' => ['view' => true, 'create' => true],
                ]);
                break;
        }
    }
}
