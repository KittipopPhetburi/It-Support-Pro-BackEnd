<?php

namespace App\Http\Controllers\Api;

use App\Models\Department;
use App\Events\DepartmentUpdated;
use Illuminate\Http\Request;

/**
 * DepartmentController - จัดการแผนก (Department Management)
 * 
 * Extends BaseCrudController + override store/update/destroy
 * โหลด branch relation + broadcast DepartmentUpdated event
 * 
 * Routes:
 * - GET    /api/departments           - รายการแผนกทั้งหมด
 * - POST   /api/departments           - สร้างแผนกใหม่ (โหลด branch)
 * - PUT    /api/departments/{id}      - แก้ไขแผนก
 * - DELETE /api/departments/{id}      - ลบแผนก
 */
class DepartmentController extends BaseCrudController
{
    protected string $modelClass = Department::class;

    protected array $validationRules = [
        'name' => 'required|string|max:255',
        'code' => 'nullable|string|max:50',
        'branch_id' => 'nullable|integer|exists:branches,id',
        'description' => 'nullable|string',
        'status' => 'nullable|in:Active,Inactive',
        'organization' => 'nullable|string|max:255',
    ];

    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules);
        $department = Department::create($data);
        $department->load('branch');

        // Broadcast event
        event(new DepartmentUpdated($department, 'created'));

        return response()->json($department, 201);
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);
        $data = $request->validate($this->validationRules);
        $department->fill($data);
        $department->save();
        $department->load('branch');

        // Broadcast event
        event(new DepartmentUpdated($department, 'updated'));

        return response()->json($department);
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        // Broadcast event
        event(new DepartmentUpdated($department, 'deleted'));

        return response()->json(null, 204);
    }
}