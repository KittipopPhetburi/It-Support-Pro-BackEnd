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
        'assigned_to' => 'nullable|string|max:255',
        'assigned_to_email' => 'nullable|email|max:255',
        'assigned_to_phone' => 'nullable|string|max:50',
        'location' => 'nullable|string|max:255',
        'ip_address' => 'nullable|string|max:45',
        'mac_address' => 'nullable|string|max:17',
        'license_key' => 'nullable|string|max:255',
        'license_type' => 'nullable|string|max:50',
        'purchase_date' => 'nullable|date',
        'start_date' => 'nullable|date',
        'warranty_expiry' => 'nullable|date',
        'expiry_date' => 'nullable|date',
        'total_licenses' => 'nullable|integer',
        'used_licenses' => 'nullable|integer',
        'branch_id' => 'nullable|integer|exists:branches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'department' => 'nullable|string|max:255',
        'organization' => 'nullable|string|max:255',
        'qr_code' => 'nullable|string|max:255',
    ];

    public function statistics()
    {
        return response()->json([
            'total' => Asset::count(),
            'available' => Asset::where('status', 'Available')->count(),
            'in_use' => Asset::where('status', 'In Use')->count(),
            'maintenance' => Asset::where('status', 'Maintenance')->count(),
            'retired' => Asset::where('status', 'Retired')->count(),
            'hardware' => Asset::where('category', 'Hardware')->count(),
            'software' => Asset::where('category', 'Software')->count(),
        ]);
    }
}
