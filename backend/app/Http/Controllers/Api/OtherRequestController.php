<?php

namespace App\Http\Controllers\Api;

use App\Models\OtherRequest;

class OtherRequestController extends BaseCrudController
{
    protected string $modelClass = OtherRequest::class;

    protected array $validationRules = [
        'requester_id' => 'required|integer|exists:users,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'category' => 'nullable|string|max:255',
        'status' => 'required|in:Pending,In Progress,Completed,Rejected',
        'request_date' => 'nullable|date',

        'branch_id' => 'nullable|integer|exists:branches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'organization' => 'nullable|string|max:255',
    ];
}
