<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
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
        $users = User::with(['branch', 'department'])->get();
        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::with(['branch', 'department'])->findOrFail($id);
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

        return response()->json($user);
    }
}