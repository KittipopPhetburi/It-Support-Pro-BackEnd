<?php

namespace App\Http\Controllers\Api;

use App\Models\AssetRequest;

class AssetRequestController extends BaseCrudController
{
    protected string $modelClass = AssetRequest::class;

    protected array $validationRules = [
        'requester_id' => 'required|integer|exists:users,id',
        'asset_type' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'justification' => 'nullable|string',
        'status' => 'required|in:Pending,Approved,Rejected,Fulfilled',
        'request_date' => 'nullable|date',

        'branch_id' => 'nullable|integer|exists:branches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'organization' => 'nullable|string|max:255',
    ];
}
