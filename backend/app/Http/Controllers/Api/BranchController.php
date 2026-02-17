<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;
use App\Events\BranchUpdated;
use Illuminate\Http\Request;

/**
 * BranchController - จัดการสาขา (Branch Management)
 * 
 * Extends BaseCrudController + override store/update/destroy
 * ทุก method broadcast BranchUpdated event
 * 
 * Routes:
 * - GET    /api/branches           - รายการสาขาทั้งหมด
 * - POST   /api/branches           - สร้างสาขาใหม่
 * - PUT    /api/branches/{id}      - แก้ไขสาขา
 * - DELETE /api/branches/{id}      - ลบสาขา
 */
class BranchController extends BaseCrudController
{
    protected string $modelClass = Branch::class;

    protected array $validationRules = [
        'name' => 'required|string|max:255',
        'code' => 'nullable|string|max:50',
        'address' => 'nullable|string',
        'province' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:50',
        'organization' => 'nullable|string|max:255',
        'status' => 'nullable|in:Active,Inactive',
    ];

    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules);
        $branch = Branch::create($data);

        // Broadcast event
        event(new BranchUpdated($branch, 'created'));

        return response()->json($branch, 201);
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $data = $request->validate($this->validationRules);
        $branch->fill($data);
        $branch->save();

        // Broadcast event
        event(new BranchUpdated($branch, 'updated'));

        return response()->json($branch);
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        // Broadcast event
        event(new BranchUpdated($branch, 'deleted'));

        return response()->json(null, 204);
    }
}