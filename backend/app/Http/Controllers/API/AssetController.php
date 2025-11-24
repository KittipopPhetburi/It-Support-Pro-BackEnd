<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetAssignment;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:assets',
            'name' => 'required|string',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'asset_status_id' => 'required|exists:asset_statuses,id',
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        $asset = Asset::create($validated);

        return response()->json($asset, 201);
    }

    public function assign(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $validated['asset_id'] = $asset->id;
        $validated['assignment_type'] = 'user';
        $validated['created_at'] = now();

        $assignment = AssetAssignment::create($validated);

        return response()->json($assignment, 201);
    }

    public function unassign(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'end_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        // Find active assignment
        $assignment = $asset->assignments()->whereNull('end_date')->firstOrFail();
        $assignment->update($validated);

        return response()->json($assignment);
    }
}
