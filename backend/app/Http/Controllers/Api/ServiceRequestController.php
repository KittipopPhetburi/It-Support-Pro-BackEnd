<?php

namespace App\Http\Controllers\Api;

use App\Models\ServiceRequest;

class ServiceRequestController extends BaseCrudController
{
    protected string $modelClass = ServiceRequest::class;

    protected array $validationRules = [
        'service_id' => 'required|integer|exists:service_catalog_items,id',
        'service_name' => 'required|string|max:255',
        'requester_id' => 'required|integer|exists:users,id',
        'status' => 'required|in:Pending,Approved,In Progress,Completed,Rejected',
        'request_date' => 'nullable|date',
        'completion_date' => 'nullable|date',

        'branch_id' => 'nullable|integer|exists:branches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'organization' => 'nullable|string|max:255',
    ];
}
