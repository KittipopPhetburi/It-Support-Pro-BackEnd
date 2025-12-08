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
            'role' => $role,
            'permissions' => $result,
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

        DB::transaction(function () use ($validated, $role) {
            foreach ($validated['permissions'] as $item) {
                RoleMenuPermission::updateOrCreate(
                    [
                        'role_id' => $role->id,
                        'menu_id' => $item['menu_id'],
                    ],
                    [
                        'can_view' => $item['can_view'] ?? false,
                        'can_create' => $item['can_create'] ?? false,
                        'can_update' => $item['can_update'] ?? false,
                        'can_delete' => $item['can_delete'] ?? false,
                    ]
                );
            }
        });

        return response()->json([
            'message' => 'บันทึกสิทธิ์สำเร็จ',
        ]);
    }
}
