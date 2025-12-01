<?php

namespace App\Http\Controllers\Api;

use App\Models\SubContractor;

class SubContractorController extends BaseCrudController
{
    protected string $modelClass = SubContractor::class;

    protected array $validationRules = [
        'name' => 'required|string|max:255',
        'company' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:50',
        'specialization' => 'nullable|array',
        'specialization.*' => 'string',
        'status' => 'required|in:Active,Inactive',
    ];
}
