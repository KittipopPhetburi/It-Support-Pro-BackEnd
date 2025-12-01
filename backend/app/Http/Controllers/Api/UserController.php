<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends BaseCrudController
{
    protected string $modelClass = User::class;

    protected array $validationRules = [
        'name' => 'required|string|max:255',
        'username' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'password' => 'required|string|min:6',
        'role' => 'required|in:Admin,Technician,Helpdesk,Purchase,User',
        'branch_id' => 'nullable|integer|exists:branches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'organization' => 'nullable|string|max:255',
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
    ];

    // override store/update เพื่อแฮชรหัสผ่าน
    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules);
        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate($this->updateValidationRules);

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->fill($data);
        $user->save();

        return response()->json($user);
    }
}
