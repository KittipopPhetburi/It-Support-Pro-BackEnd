<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;

class BranchController extends BaseCrudController
{
    protected string $modelClass = Branch::class;

    protected array $validationRules = [
        'name' => 'required|string|max:255',
        'address' => 'nullable|string',
        'organization' => 'nullable|string|max:255',
    ];
}
