<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use App\Models\Role;
use App\Models\RoleMenuPermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuPermission
{
    /**
     * Usage: ->middleware('menu.permission:incident_management,view')
     */
    public function handle(Request $request, Closure $next, string $menuKey, string $ability = 'view'): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Map user role (enum on users table) to roles table
        $role = Role::where('name', $user->role)->first();
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 403);
        }

        $menu = Menu::where('key', $menuKey)->first();
        if (!$menu) {
            return response()->json(['message' => 'Menu not found'], 404);
        }

        $permission = RoleMenuPermission::where('role_id', $role->id)
            ->where('menu_id', $menu->id)
            ->first();

        $allowed = match ($ability) {
            'view' => $permission?->can_view,
            'create' => $permission?->can_create,
            'update' => $permission?->can_update,
            'delete' => $permission?->can_delete,
            default => false,
        } ?? false;

        if (!$allowed) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
