<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::query();

        if ($request->has('asset_category_id')) {
            $query->where('asset_category_id', $request->asset_category_id);
        }

        if ($request->has('asset_status_id')) {
            $query->where('asset_status_id', $request->asset_status_id);
        }

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        return response()->json($query->with(['category', 'status', 'branch', 'department', 'vendor'])->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:assets,code',
            'name' => 'required',
            'description' => 'nullable',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'asset_status_id' => 'required|exists:asset_statuses,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'serial_number' => 'nullable',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'warranty_expiry_date' => 'nullable|date',
        ]);

        $asset = Asset::create($validated);
        return response()->json($asset, 201);
    }

    public function show(Asset $asset)
    {
        return response()->json($asset->load(['category', 'status', 'branch', 'department', 'vendor', 'assignments.user', 'maintenanceContracts']));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'code' => 'required|unique:assets,code,' . $asset->id,
            'name' => 'required',
            'description' => 'nullable',
            'asset_category_id' => 'sometimes|exists:asset_categories,id',
            'asset_status_id' => 'sometimes|exists:asset_statuses,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'serial_number' => 'nullable',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'warranty_expiry_date' => 'nullable|date',
        ]);

        $asset->update($validated);
        return response()->json($asset);
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return response()->json(null, 204);
    }

    public function assign(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'note' => 'nullable',
        ]);

        // Check if already assigned
        $currentAssignment = $asset->assignments()->whereNull('end_date')->first();
        if ($currentAssignment) {
            return response()->json(['message' => 'Asset is already assigned'], 400);
        }

        $assignment = $asset->assignments()->create([
            'user_id' => $validated['user_id'],
            'assignment_type' => 'user',
            'start_date' => $validated['start_date'],
            'note' => $validated['note'] ?? null,
        ]);

        return response()->json($assignment, 201);
    }

    public function unassign(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'end_date' => 'required|date',
            'note' => 'nullable',
        ]);

        $currentAssignment = $asset->assignments()->whereNull('end_date')->first();
        if (!$currentAssignment) {
            return response()->json(['message' => 'Asset is not currently assigned'], 400);
        }

        $currentAssignment->update([
            'end_date' => $validated['end_date'],
            'note' => ($currentAssignment->note ? $currentAssignment->note . "\n" : "") . ($validated['note'] ?? ""),
        ]);

        return response()->json($currentAssignment);
    }
}
