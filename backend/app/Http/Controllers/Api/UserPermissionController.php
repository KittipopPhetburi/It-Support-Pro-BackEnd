<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\User;
use App\Models\RoleMenuPermission;
use App\Models\UserMenuPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserPermissionController extends Controller
{
    /**
     * GET /api/users/{userId}/permissions
     * Get merged permissions (role + user override) for a specific user
     */
    public function index(int $userId)
    {
        $user = User::findOrFail($userId);
        $role = \App\Models\Role::where('name', $user->role)->first();

        // Load all menus
        $menus = Menu::orderBy('group')->orderBy('sort_order')->get();
        
        // Get role permissions
        $rolePermissions = $role 
            ? RoleMenuPermission::where('role_id', $role->id)->get()->keyBy('menu_id')
            : collect();
        
        // Get user-specific permissions
        $userPermissions = UserMenuPermission::where('user_id', $user->id)->get()->keyBy('menu_id');

        $result = $menus->map(function (Menu $menu) use ($rolePermissions, $userPermissions) {
            $rolePerm = $rolePermissions->get($menu->id);
            $userPerm = $userPermissions->get($menu->id);
            
            // User permission overrides role permission if exists
            $hasUserOverride = $userPerm !== null;
            
            return [
                'menu_id' => $menu->id,
                'menu_key' => $menu->key,
                'menu_name' => $menu->name,
                'menu_group' => $menu->group,
                // Effective permission (user override wins)
                'can_view' => $hasUserOverride ? $userPerm->can_view : ($rolePerm?->can_view ?? false),
                'can_create' => $hasUserOverride ? $userPerm->can_create : ($rolePerm?->can_create ?? false),
                'can_update' => $hasUserOverride ? $userPerm->can_update : ($rolePerm?->can_update ?? false),
                'can_delete' => $hasUserOverride ? $userPerm->can_delete : ($rolePerm?->can_delete ?? false),
                // Additional info
                'has_user_override' => $hasUserOverride,
                'role_can_view' => $rolePerm?->can_view ?? false,
                'role_can_create' => $rolePerm?->can_create ?? false,
                'role_can_update' => $rolePerm?->can_update ?? false,
                'role_can_delete' => $rolePerm?->can_delete ?? false,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                ],
                'permissions' => $result,
            ],
        ]);
    }

    /**
     * PUT /api/users/{userId}/permissions
     * Update user-specific permissions (override role)
     */
    public function update(Request $request, int $userId)
    {
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*.menu_id' => 'required|exists:menus,id',
            'permissions.*.can_view' => 'boolean',
            'permissions.*.can_create' => 'boolean',
            'permissions.*.can_update' => 'boolean',
            'permissions.*.can_delete' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $user, $request) {
            $username = $request->user()?->name ?? 'System';
            
            foreach ($validated['permissions'] as $item) {
                $menu = Menu::find($item['menu_id']);
                
                UserMenuPermission::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'menu_id' => $item['menu_id'],
                    ],
                    [
                        'user_name' => $user->name,
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
            'message' => 'บันทึกสิทธิ์รายบุคคลสำเร็จ',
        ]);
    }

    /**
     * POST /api/users/{userId}/permissions/reset
     * Reset user-specific permissions to role defaults (delete all overrides)
     */
    public function reset(int $userId)
    {
        $user = User::findOrFail($userId);
        
        // Delete all user-specific permissions
        UserMenuPermission::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'รีเซ็ตสิทธิ์เป็นค่าจาก Role สำเร็จ',
        ]);
    }
}
