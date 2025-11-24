<?php

namespace App\Http\Controllers;

use App\Models\AssetRequest;
use App\Models\AssetRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetRequest::query();

        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->has('requester_id')) {
            $query->where('requester_id', $request->requester_id);
        }

        return response()->json($query->with(['status', 'requester', 'items.category'])->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_type' => 'required',
            'reason' => 'nullable',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'items' => 'required|array|min:1',
            'items.*.asset_category_id' => 'required|exists:asset_categories,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.specification' => 'nullable',
            'items.*.budget_per_item' => 'nullable|numeric',
        ]);

        // Default status: 1 (New/Draft) - assuming ID 1 exists
        $statusId = 1; 

        $assetRequest = DB::transaction(function () use ($validated, $request, $statusId) {
            $assetRequest = AssetRequest::create([
                'code' => 'AR-' . time(),
                'requester_id' => $request->user()->id ?? 1,
                'branch_id' => $validated['branch_id'] ?? null,
                'department_id' => $validated['department_id'] ?? null,
                'status_id' => $statusId,
                'request_type' => $validated['request_type'],
                'reason' => $validated['reason'] ?? null,
                'requested_date' => now(),
            ]);

            foreach ($validated['items'] as $item) {
                AssetRequestItem::create([
                    'asset_request_id' => $assetRequest->id,
                    'asset_category_id' => $item['asset_category_id'],
                    'quantity' => $item['quantity'],
                    'specification' => $item['specification'] ?? null,
                    'budget_per_item' => $item['budget_per_item'] ?? null,
                ]);
            }

            return $assetRequest;
        });

        return response()->json($assetRequest->load('items'), 201);
    }

    public function show(AssetRequest $assetRequest)
    {
        return response()->json($assetRequest->load(['status', 'requester', 'approver', 'items.category']));
    }

    public function update(Request $request, AssetRequest $assetRequest)
    {
        // Only allow update if in draft/new status? For now, allow basic updates.
        $validated = $request->validate([
            'reason' => 'nullable',
            'request_type' => 'sometimes|required',
        ]);

        $assetRequest->update($validated);
        return response()->json($assetRequest);
    }

    public function approve(Request $request, AssetRequest $assetRequest)
    {
        // Logic to approve
        // Update status to approved (ID 2?)
        // Set approver_id and approved_date
        
        $assetRequest->update([
            'status_id' => 2, // Assuming 2 is Approved
            'approver_id' => $request->user()->id ?? 1,
            'approved_date' => now(),
        ]);

        return response()->json($assetRequest);
    }

    public function reject(Request $request, AssetRequest $assetRequest)
    {
        // Logic to reject
        // Update status to rejected (ID 3?)
        
        $assetRequest->update([
            'status_id' => 3, // Assuming 3 is Rejected
            'approver_id' => $request->user()->id ?? 1,
            'approved_date' => now(), // Decision date
        ]);

        return response()->json($assetRequest);
    }
}
