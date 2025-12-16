<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Role;
use App\Models\RoleMenuPermission;
use App\Events\UserUpdated;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends BaseCrudController
{
    protected string $modelClass = User::class;

    protected array $validationRules = [
        'name' => 'required|string|max:255',
        'username' => 'required|string|max:255|unique:users,username',
        'email' => 'required|email|max:255|unique:users,email',
        'password' => 'required|string|min:6',
        'role' => 'required|in:Admin,Technician,Helpdesk,Purchase,User',
        'branch_id' => 'nullable|integer|exists:branches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'organization' => 'sometimes|nullable|string|max:255',
        'phone' => 'sometimes|nullable|string|max:50',
        'status' => 'sometimes|nullable|string|in:Active,Inactive',
    ];

    protected array $updateValidationRules = [
        'name' => 'sometimes|required|string|max:255',
        'username' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|max:255',
        'password' => 'sometimes|nullable|string|min:6',
        'role' => 'sometimes|required|in:Admin,Technician,Helpdesk,Purchase,User',
        'branch_id' => 'sometimes|nullable|integer|exists:branches,id',
        'department_id' => 'sometimes|nullable|integer|exists:departments,id',
        'organization' => 'sometimes|nullable|string|max:255',
        'phone' => 'sometimes|nullable|string|max:50',
        'status' => 'sometimes|nullable|string|in:Active,Inactive',
    ];

    public function index(Request $request)
    {
        $query = User::with(['branch', 'department']);
        
        // Filter by role if provided
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $users = $query->orderBy('name')->get();
        
        // Attach role permissions to each user
        $users = $users->map(function ($user) {
            return $this->attachRolePermissions($user);
        });
        
        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::with(['branch', 'department'])->findOrFail($id);
        $user = $this->attachRolePermissions($user);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules);

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        if (!isset($data['status'])) {
            $data['status'] = 'Active';
        }

        $user = User::create($data);
        $user->load(['branch', 'department']);

        // Broadcast event
        event(new UserUpdated($user, 'created'));

        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = $this->updateValidationRules;

        $rules['username'] = Rule::unique('users', 'username')->ignore($user->id);
        $rules['email'] = Rule::unique('users', 'email')->ignore($user->id);

        $data = $request->validate($rules);

        if (array_key_exists('password', $data)) {
            if (!empty($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            } else {
                unset($data['password']);
            }
        }

        $user->fill($data);
        $user->save();
        $user->load(['branch', 'department']);

        // Broadcast event
        event(new UserUpdated($user, 'updated'));

        return response()->json($user);
    }

    public function getTechnicians()
    {
        $technicians = User::where('role', 'Technician')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($technicians);
    }

    /**
     * Helper: Attach role permissions to user
     */
    private function attachRolePermissions(User $user)
    {
        $role = Role::where('name', $user->role)->first();
        
        if ($role) {
            $permissions = RoleMenuPermission::where('role_id', $role->id)
                ->with('menu')
                ->get()
                ->filter(function ($perm) {
                    return $perm->menu !== null;
                })
                ->map(function ($perm) {
                    return [
                        'menu_id' => $perm->menu_id,
                        'menu_key' => $perm->menu->key,
                        'menu_name' => $perm->menu->name,
                        'menu_group' => $perm->menu->group,
                        'can_view' => (bool) $perm->can_view,
                        'can_create' => (bool) $perm->can_create,
                        'can_update' => (bool) $perm->can_update,
                        'can_delete' => (bool) $perm->can_delete,
                    ];
                })
                ->values();
            
            $user->role_permissions = $permissions;
        } else {
            $user->role_permissions = [];
        }
        
        return $user;
    }
}
