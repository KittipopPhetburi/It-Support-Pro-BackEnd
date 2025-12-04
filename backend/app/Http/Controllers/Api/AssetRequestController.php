<?php

namespace App\Http\Controllers\Api;

use App\Models\AssetRequest;

class AssetRequestController extends BaseCrudController
{
    protected string $modelClass = AssetRequest::class;

    protected array $validationRules = [
        'requester_id' => 'nullable|integer|exists:users,id',
        'requester_name' => 'nullable|string|max:255',
        'request_type' => 'nullable|string|in:Requisition,Borrow,Replace',
        'asset_type' => 'required|string|max:255',
        'asset_id' => 'nullable|integer|exists:assets,id',
        'quantity' => 'nullable|integer|min:1',
        'justification' => 'nullable|string',
        'reason' => 'nullable|string',
        'status' => 'nullable|in:Pending,Approved,Rejected,Fulfilled,Received,Cancelled',
        'request_date' => 'nullable|date',
        'branch_id' => 'nullable|integer|exists:branches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'department' => 'nullable|string|max:255',
        'organization' => 'nullable|string|max:255',
        'approved_at' => 'nullable|date',
        'approved_by' => 'nullable|string|max:255',
        'rejected_at' => 'nullable|date',
        'rejected_by' => 'nullable|string|max:255',
        'reject_reason' => 'nullable|string',
        'received_at' => 'nullable|date',
    ];

    public function statistics()
    {
        return response()->json([
            'total' => AssetRequest::count(),
            'pending' => AssetRequest::where('status', 'Pending')->count(),
            'approved' => AssetRequest::where('status', 'Approved')->count(),
            'rejected' => AssetRequest::where('status', 'Rejected')->count(),
            'fulfilled' => AssetRequest::where('status', 'Fulfilled')->count(),
            'received' => AssetRequest::where('status', 'Received')->count(),
        ]);
    }

    public function myRequests()
    {
        $user = auth()->user();
        $requests = AssetRequest::where('requester_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return response()->json($requests);
    }

    public function approve(AssetRequest $assetRequest)
    {
        $assetRequest->update([
            'status' => 'Approved',
            'approved_at' => now(),
            'approved_by' => auth()->user()->name,
        ]);
        return response()->json($assetRequest);
    }

    public function reject(AssetRequest $assetRequest)
    {
        $reason = request('reason', '');
        $assetRequest->update([
            'status' => 'Rejected',
            'rejected_at' => now(),
            'rejected_by' => auth()->user()->name,
            'reject_reason' => $reason,
        ]);
        return response()->json($assetRequest);
    }
}
