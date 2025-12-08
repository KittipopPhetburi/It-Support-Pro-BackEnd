<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Register - ลงทะเบียนผู้ใช้ใหม่
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

        return response()->json([
            'message' => 'เข้าสู่ระบบสำเร็จ',
            'user' => $user->load(['branch', 'department']),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout - ออกจากระบบ
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'ออกจากระบบสำเร็จ',
        ]);
    }

    /**
     * Me - ข้อมูลผู้ใช้ปัจจุบัน
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load(['branch', 'department']),
        ]);
    }

    /**
     * Update Password - เปลี่ยนรหัสผ่าน
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
}
