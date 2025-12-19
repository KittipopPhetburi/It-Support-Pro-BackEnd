<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Menu;
use App\Models\RoleMenuPermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * GET /api/roles
     * List all roles
     */
    public function index()
    {
        $roles = Role::withCount('permissions')
            ->orderBy('id')
            ->get()
            ->map(function ($role) {
                // Count users with this role
                $userCount = User::where('role', $role->name)->count();
                $role->user_count = $userCount;
                $role->is_default = in_array($role->name, ['Admin', 'Technician', 'Helpdesk', 'Purchase', 'User']);
                return $role;
            });

        return response()->json([
            'success' => true,
            'roles' => $roles,
        ]);
    }

    /**
     * POST /api/roles
     * Create a new role with permissions
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'display_name' => 'nullable|string|max:100',
            'permissions' => 'nullable|array',
            'permissions.*.menu_id' => 'required|exists:menus,id',
            'permissions.*.can_view' => 'boolean',
            'permissions.*.can_create' => 'boolean',
            'permissions.*.can_update' => 'boolean',
            'permissions.*.can_delete' => 'boolean',
        ]);

        $role = DB::transaction(function () use ($validated, $request) {
            // Create role
            $role = Role::create([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'] ?? $validated['name'],
            ]);

            // Create permissions if provided
            if (!empty($validated['permissions'])) {
                foreach ($validated['permissions'] as $perm) {
                    $menu = Menu::find($perm['menu_id']);
                    RoleMenuPermission::create([
                        'role_id' => $role->id,
                        'menu_id' => $perm['menu_id'],
                        'role_name' => $role->display_name ?? $role->name,
                        'menu_name' => $menu?->name,
                        'can_view' => $perm['can_view'] ?? false,
                        'can_create' => $perm['can_create'] ?? false,
                        'can_update' => $perm['can_update'] ?? false,
                        'can_delete' => $perm['can_delete'] ?? false,
                        'created_by_username' => $request->user()?->name ?? 'System',
                    ]);
                }
            }

            return $role;
        });

        return response()->json([
            'success' => true,
            'message' => 'สร้าง Role สำเร็จ',
            'role' => $role,
        ], 201);
    }

    /**
     * PUT /api/roles/{role}
     * Update role name/display_name
     */
    public function update(Request $request, Role $role)
    {
        // Prevent editing default roles name (but allow display_name change)
        $isDefaultRole = in_array($role->name, ['Admin', 'Technician', 'Helpdesk', 'Purchase', 'User']);

        $validated = $request->validate([
            'name' => $isDefaultRole ? 'prohibited' : 'sometimes|string|max:50|unique:roles,name,' . $role->id,
            'display_name' => 'nullable|string|max:100',
        ]);

        $role->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'อัพเดท Role สำเร็จ',
            'role' => $role,
        ]);
    }

    /**
     * DELETE /api/roles/{role}
     * Delete a role (only if no users are using it and not a default role)
     */
    public function destroy(Role $role)
    {
        // Prevent deleting default roles
        $defaultRoles = ['Admin', 'Technician', 'Helpdesk', 'Purchase', 'User'];
        if (in_array($role->name, $defaultRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถลบ Role เริ่มต้นได้',
            ], 403);
        }

        // Check if any users are using this role
        $userCount = User::where('role', $role->name)->count();
        if ($userCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "ไม่สามารถลบ Role ได้เนื่องจากมี {$userCount} ผู้ใช้ที่ใช้ Role นี้อยู่",
            ], 400);
        }

        DB::transaction(function () use ($role) {
            // Delete associated permissions
            RoleMenuPermission::where('role_id', $role->id)->delete();
            // Delete role
            $role->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'ลบ Role สำเร็จ',
        ]);
    }
}
