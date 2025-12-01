<?php

namespace App\Http\Controllers\Api;

use App\Models\Incident;

class IncidentController extends BaseCrudController
{
    protected string $modelClass = Incident::class;

    protected array $validationRules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'priority' => 'required|in:Low,Medium,High,Critical',
        'status' => 'required|in:Open,In Progress,Pending,Resolved,Closed',
        'category' => 'nullable|string|max:255',
        'subcategory' => 'nullable|string|max:255',

        'requester_id' => 'required|integer|exists:users,id',
        'reported_by_id' => 'nullable|integer|exists:users,id',
        'assignee_id' => 'nullable|integer|exists:users,id',

        'resolved_at' => 'nullable|date',
        'closed_at' => 'nullable|date',

        'branch_id' => 'nullable|integer|exists:branches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'organization' => 'nullable|string|max:255',

        'contact_method' => 'nullable|string|max:255',
        'contact_phone' => 'nullable|string|max:50',
        'location' => 'nullable|string|max:255',

        'asset_id' => 'nullable|integer|exists:assets,id',
        'asset_name' => 'nullable|string|max:255',
        'asset_brand' => 'nullable|string|max:255',
        'asset_model' => 'nullable|string|max:255',
        'asset_serial_number' => 'nullable|string|max:255',
        'asset_inventory_number' => 'nullable|string|max:255',
        'is_custom_asset' => 'nullable|boolean',
        'equipment_type' => 'nullable|string|max:255',
        'operating_system' => 'nullable|string|max:255',

        'start_repair_date' => 'nullable|date',
        'completion_date' => 'nullable|date',
        'repair_details' => 'nullable|string',
        'repair_status' => 'nullable|string|max:255',
        'replacement_equipment' => 'nullable|string|max:255',
        'has_additional_cost' => 'nullable|boolean',
        'additional_cost' => 'nullable|numeric',

        'technician_signature' => 'nullable|string',
        'customer_signature' => 'nullable|string',

        'satisfaction_rating' => 'nullable|integer|min:1|max:5',
        'satisfaction_comment' => 'nullable|string',
        'satisfaction_date' => 'nullable|date',
    ];
}
