<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;

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
}