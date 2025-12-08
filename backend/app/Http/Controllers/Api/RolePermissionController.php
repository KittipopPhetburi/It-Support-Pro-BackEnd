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
                $applyPreset($menus->mapWithKeys(fn($m) => [$m->key => [
                    'view' => true, 'create' => true, 'update' => true, 'delete' => true
                ]])->toArray());
                break;

            case 'Technician':
                $applyPreset([
                    'dashboard_service' => ['view' => true],
                    'incident_management' => ['view' => true, 'update' => true],
                    'service_catalog' => ['view' => true],
                    'problem_management' => ['view' => true, 'update' => true],
                    'dashboard_equipment' => ['view' => true],
                    'asset_management' => ['view' => true, 'update' => true],
                    'asset' => ['view' => true, 'update' => true],
                    'asset_request' => ['view' => true],
                    'other_request' => ['view' => true],
                    'satisfaction_kpi' => ['view' => true],
                ]);
                break;

            case 'Helpdesk':
                $applyPreset([
                    'dashboard_service' => ['view' => true],
                    'incident_management' => ['view' => true, 'create' => true],
                    'service_catalog' => ['view' => true],
                    'problem_management' => ['view' => true],
                    'dashboard_equipment' => ['view' => true],
                    'asset_management' => ['view' => true],
                    'asset' => ['view' => true],
                    'asset_request' => ['view' => true, 'create' => true],
                    'other_request' => ['view' => true, 'create' => true],
                    'satisfaction_kpi' => ['view' => true],
                ]);
                break;

            case 'Purchase':
                $applyPreset([
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
                    'sub_contract_management' => ['view' => true],
                    'satisfaction_kpi' => ['view' => true],
                ]);
                break;

            case 'User':
                $applyPreset([
                    'dashboard_service' => ['view' => true],
                    'incident_management' => ['view' => true, 'create' => true],
                    'asset_request' => ['view' => true, 'create' => true],
                    'other_request' => ['view' => true, 'create' => true],
                ]);
                break;
        }
    }
}
