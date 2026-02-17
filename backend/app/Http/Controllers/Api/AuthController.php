<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\RoleMenuPermission;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

use App\Models\UserMenuPermission;

/**
 * AuthController - ระบบยืนยันตัวตน (Authentication)
 * 
 * จัดการ:
 * - register: ลงทะเบียนผู้ใช้ใหม่ + ออก token
 * - login: เข้าสู่ระบบ (รองรับ email หรือ username) + ออก token + แนบ permissions
 * - logout: ออกจากระบบ (ลบ token)
 * - me: ดึงข้อมูลผู้ใช้ปัจจุบัน + permissions
 * - updatePassword: เปลี่ยนรหัสผ่าน (ต้องยืนยันรหัสเดิม)
 * 
 * ใช้ Laravel Sanctum สำหรับ Token-based Authentication
 * 
 * Routes:
 * - POST /api/register (public)
 * - POST /api/login (public)
 * - POST /api/logout (auth)
 * - GET  /api/me (auth)
 * - PUT  /api/password (auth)
 */
class AuthController extends Controller
{
    /**
     * Register - ลงทะเบียนผู้ใช้ใหม่
     * 
     * POST /api/register (public - ไม่ต้อง login)
     * 
     * @param Request $request {name, username, email, password, password_confirmation, role?, branch_id?, department_id?, organization?}
     * @return JsonResponse 201 {message, user, token, token_type}
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'sometimes|string|in:admin,manager,technician,user',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'organization' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'user',
            'branch_id' => $validated['branch_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'organization' => $validated['organization'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'ลงทะเบียนสำเร็จ',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Login - เข้าสู่ระบบ
     * 
     * POST /api/login (public - ไม่ต้อง login)
     * รองรับ identifier ทั้ง email และ username
     * สร้าง Sanctum token + load branch/department + แนบ permissions
     * 
     * @param Request $request {identifier (email หรือ username), password}
     * @return JsonResponse {message, user (+ permissions), token, token_type} หรือ 401
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'identifier' => 'required|string', // email or username
            'password' => 'required',
        ]);

        $identifier = $validated['identifier'];
        $password = $validated['password'];

        // Determine if identifier is an email address
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $credentials = ['email' => $identifier, 'password' => $password];
            $user = User::where('email', $identifier)->first();
        } else {
            $credentials = ['username' => $identifier, 'password' => $password];
            $user = User::where('username', $identifier)->first();
        }

        if (!$user || !Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'ชื่อผู้ใช้/อีเมล หรือ รหัสผ่านไม่ถูกต้อง',
            ], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        // Load role permissions
        $user->load(['branch', 'department']);
        $user = $this->attachMergedPermissions($user);

        return response()->json([
            'message' => 'เข้าสู่ระบบสำเร็จ',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout - ออกจากระบบ
     * 
     * POST /api/logout (auth:sanctum)
     * ลบ token ปัจจุบันของผู้ใช้
     * 
     * @return JsonResponse {message}
     */
    public function logout(Request $request)
    {
        // Check if the token is a real PersonalAccessToken (not TransientToken from actingAs)
        $token = $request->user()->currentAccessToken();
        
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json([
            'message' => 'ออกจากระบบสำเร็จ',
        ]);
    }

    /**
     * Me - ดึงข้อมูลผู้ใช้ที่ login อยู่
     * 
     * GET /api/me (auth:sanctum)
     * โหลด branch, department พร้อม permissions (role + user override)
     * 
     * @return JsonResponse {user (+ role_permissions)}
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['branch', 'department']);
        $user = $this->attachMergedPermissions($user);
        
        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Update Password - เปลี่ยนรหัสผ่าน
     * 
     * PUT /api/password (auth:sanctum)
     * ต้องยืนยันรหัสผ่านปัจจุบัน (current_password) ก่อนเปลี่ยน
     * 
     * @param Request $request {current_password, password, password_confirmation}
     * @return JsonResponse {message} หรือ 400 (รหัสผ่านไม่ถูกต้อง)
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง',
            ], 400);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'เปลี่ยนรหัสผ่านสำเร็จ',
        ]);
    }

    /**
     * Helper: รวม permissions จาก Role + User override แล้วแนบไปกับ user object
     * 
     * ลำดับความสำคัญ:
     * 1. ดึง permissions จาก Role (base)
     * 2. ดึง permissions เฉพาะ User (override)
     * 3. ถ้า user มี override → ใช้ค่า override, ไม่มี → ใช้ค่าจาก role
     * 
     * ผลลัพธ์: $user->role_permissions = [{menu_id, menu_key, can_view, can_create, can_update, can_delete, has_user_override}]
     * 
     * @param User $user
     * @return User พร้อม role_permissions
     */
    private function attachMergedPermissions(User $user)
    {
        $role = Role::where('name', $user->role)->first();
        $menus = Menu::orderBy('group')->orderBy('sort_order')->get();
        
        // Get role permissions (base)
        $rolePermissions = $role 
            ? RoleMenuPermission::where('role_id', $role->id)->get()->keyBy('menu_id')
            : collect();
        
        // Get user-specific permissions (override)
        $userPermissions = UserMenuPermission::where('user_id', $user->id)->get()->keyBy('menu_id');
        
        // Merge: user permissions override role permissions
        $mergedPermissions = $menus->map(function ($menu) use ($rolePermissions, $userPermissions) {
            $rolePerm = $rolePermissions->get($menu->id);
            $userPerm = $userPermissions->get($menu->id);
            
            // User permission overrides role permission if exists
            $hasUserOverride = $userPerm !== null;
            
            return [
                'menu_id' => $menu->id,
                'menu_key' => $menu->key,
                'menu_name' => $menu->name,
                'menu_group' => $menu->group,
                'can_view' => $hasUserOverride ? (bool) $userPerm->can_view : (bool) ($rolePerm?->can_view ?? false),
                'can_create' => $hasUserOverride ? (bool) $userPerm->can_create : (bool) ($rolePerm?->can_create ?? false),
                'can_update' => $hasUserOverride ? (bool) $userPerm->can_update : (bool) ($rolePerm?->can_update ?? false),
                'can_delete' => $hasUserOverride ? (bool) $userPerm->can_delete : (bool) ($rolePerm?->can_delete ?? false),
                'has_user_override' => $hasUserOverride,
            ];
        })->values();
        
        $user->role_permissions = $mergedPermissions;
        
        return $user;
    }
}

