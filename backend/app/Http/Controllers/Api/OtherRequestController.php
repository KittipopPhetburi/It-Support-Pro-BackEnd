<?php

namespace App\Http\Controllers\Api;

use App\Models\OtherRequest;

class OtherRequestController extends BaseCrudController
{
    protected string $modelClass = OtherRequest::class;

    protected array $validationRules = [
        'requester_id' => 'nullable|integer|exists:users,id',
        'requester_name' => 'nullable|string|max:255',
        'title' => 'required|string|max:255',
        'item_name' => 'nullable|string|max:255',
        'item_type' => 'nullable|string|max:255',
        'request_type' => 'nullable|string|in:Requisition,Borrow,Replace',
        'quantity' => 'nullable|integer|min:1',
        'unit' => 'nullable|string|max:50',
        'description' => 'nullable|string',
        'reason' => 'nullable|string',
        'category' => 'nullable|string|max:255',
        'status' => 'required|in:Pending,Approved,In Progress,Completed,Received,Rejected',
        'request_date' => 'nullable|date',
        'branch_id' => 'nullable|integer|exists:branches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'organization' => 'nullable|string|max:255',
        'department' => 'nullable|string|max:255',
        'approved_by' => 'nullable|string|max:255',
        'approved_at' => 'nullable|date',
        'rejected_by' => 'nullable|string|max:255',
        'rejected_at' => 'nullable|date',
        'reject_reason' => 'nullable|string',
        'completed_by' => 'nullable|string|max:255',
        'completed_at' => 'nullable|date',
        'received_at' => 'nullable|date',
        'brand' => 'nullable|string|max:255',
        'model' => 'nullable|string|max:255',
    ];
}