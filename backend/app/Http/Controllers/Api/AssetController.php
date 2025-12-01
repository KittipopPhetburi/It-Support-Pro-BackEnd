<?php

namespace App\Http\Controllers\Api;

use App\Models\Asset;

class AssetController extends BaseCrudController
{
    protected string $modelClass = Asset::class;

    protected array $validationRules = [
        'name' => 'required|string|max:255',
        'type' => 'required|string|max:255',
        'category' => 'nullable|string|max:255',
        'brand' => 'nullable|string|max:255',
        'model' => 'nullable|string|max:255',
        'serial_number' => 'required|string|max:255',
        'inventory_number' => 'nullable|string|max:255',
        'status' => 'required|in:Available,In Use,Maintenance,Retired',
        'assigned_to_id' => 'nullable|integer|exists:users,id',
        'location' => 'nullable|string|max:255',
        'purchase_date' => 'nullable|date',
        'warranty_expiry' => 'nullable|date',
        'branch_id' => 'nullable|integer|exists:branches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'organization' => 'nullable|string|max:255',
    ];
}
