<?php

namespace App\Http\Controllers\Api;

use App\Models\Department;

class DepartmentController extends BaseCrudController
{
    protected string $modelClass = Department::class;

    protected array $validationRules = [
        'name' => 'required|string|max:255',
        'branch_id' => 'nullable|integer|exists:branches,id',
        'organization' => 'nullable|string|max:255',
    ];
}
