<?php

namespace App\Http\Controllers\Api;

use App\Models\AssetRequest;
use App\Events\AssetRequestUpdated;

class AssetRequestController extends BaseCrudController
{
    protected string $modelClass = AssetRequest::class;

    protected array $validationRules = [
        'requester_id' => 'nullable|integer',
        'requester_name' => 'nullable|string|max:255',
        'request_type' => 'nullable|string|in:Requisition,Borrow,Replace',
        'asset_type' => 'required|string|max:255',
        'asset_id' => 'nullable|integer',
        'quantity' => 'nullable|integer|min:1',
        'justification' => 'nullable|string',
        'reason' => 'nullable|string',
        'status' => 'nullable|in:Pending,Approved,Rejected,Fulfilled,Received,Cancelled',
        'request_date' => 'nullable|date',
        'branch_id' => 'nullable|integer',
        'department_id' => 'nullable|integer',
        'department' => 'nullable|string|max:255',
        'organization' => 'nullable|string|max:255',
        'approved_at' => 'nullable|date',
        'approved_by' => 'nullable|string|max:255',
        'rejected_at' => 'nullable|date',
        'rejected_by' => 'nullable|string|max:255',
        'reject_reason' => 'nullable|string',
        'received_at' => 'nullable|date',
    ];

    public function index(\Illuminate\Http\Request $request)
    {
        $query = AssetRequest::with('requester', 'asset', 'branch', 'departmentRelation')
            ->orderBy('created_at', 'desc');

        // Support pagination
        if ($request->has('per_page')) {
            $requests = $query->paginate((int) $request->get('per_page', 15));
            return response()->json($requests);
        }

        $requests = $query->get();
        return response()->json(['data' => $requests]);
    }

    public function show($id)
    {
        $request = AssetRequest::with('requester', 'asset', 'branch', 'departmentRelation')
            ->findOrFail($id);

        return response()->json($request);
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $data = $this->validationRules
            ? $request->validate($this->validationRules)
            : $request->all();

        $model = AssetRequest::create($data);
        
        // Reload with relationships
        $model = AssetRequest::with('requester', 'asset', 'branch', 'departmentRelation')
            ->findOrFail($model->id);

        // Broadcast event
        event(new AssetRequestUpdated($model, 'created'));

        return response()->json($model, 201);
    }

    public function update(\Illuminate\Http\Request $request, $id)
    {
        $model = AssetRequest::findOrFail($id);

        $rules = $this->updateValidationRules ?: $this->validationRules;

        $data = $rules
            ? $request->validate($rules)
            : $request->all();

        $model->fill($data);
        $model->save();

        // Reload with relationships
        $model = AssetRequest::with('requester', 'asset', 'branch', 'departmentRelation')
            ->findOrFail($model->id);

        // Broadcast event
        event(new AssetRequestUpdated($model, 'updated'));

        return response()->json($model);
    }

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
        $requests = AssetRequest::with('requester', 'asset', 'branch', 'departmentRelation')
            ->where('requester_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $requests->getCollection()->transform(function ($request) {
            return $this->addRequesterName($request);
        });

        return response()->json($requests);
    }

    public function approve(AssetRequest $assetRequest)
    {
        // Update Asset status and assigned user if an asset is linked to this request
        $borrowedSerial = null;
        
        if ($assetRequest->asset_id) {
            $asset = \App\Models\Asset::find($assetRequest->asset_id);
            if ($asset) {
                // Check if Asset has specific serial numbers/license keys to manage
                $hasSerials = !empty($asset->serial_number);

                if (!$hasSerials && $asset->category === 'Software') {
                    // Logic for Software WITHOUT specific license keys (Count based)
                    $availableLicenses = ($asset->total_licenses ?? 0) - ($asset->used_licenses ?? 0);
                    if ($availableLicenses <= 0) {
                        return response()->json([
                            'error' => 'License สำหรับซอฟต์แวร์นี้หมดแล้ว (No available licenses)',
                        ], 422);
                    }
                    // Increment used licenses
                    $asset->used_licenses = ($asset->used_licenses ?? 0) + 1;
                    
                } else {
                    // Logic for Assets WITH serials/keys (Hardware OR Software with keys)
                    // Get first available serial (FIFO)
                    $borrowedSerial = $asset->getFirstAvailableSerial();
                    
                    if (!$borrowedSerial) {
                        return response()->json([
                            'error' => 'ไม่มี Serial Number/License Key ที่ว่างสำหรับทรัพย์สินนี้ (No available items)',
                        ], 422);
                    }
                    
                }

                // Update request FIRST to ensure availability calculation is accurate
                $assetRequest->update([
                    'status' => 'Approved',
                    'approved_at' => now(),
                    'approved_by' => auth()->user()->name,
                    'borrowed_serial' => $borrowedSerial,
                ]);

                // Determine new status and update Asset
                // Refresh asset to ensure we get latest availability from DB (including the just-approved request)
                $asset = $asset->fresh(); 
                $availableQty = $asset->available_quantity;
                
                // If this was the last item, mark as In Use / Out of Stock
                if ($availableQty <= 0) {
                     $asset->status = 'In Use';  
                } else {
                     $asset->status = 'Available';
                }
                
                // For hardware, if we assigned a serial, we might want to track who holds it, 
                // but since 'assigned_to' on Asset is single-value, for multi-serial we rely on Request history.
                // However, if quantity=1 (single asset), we can still update assigned_to for backward compatibility.
                if ($asset->quantity == 1) {
                     $requesterName = $assetRequest->requester_name ?? \App\Models\User::find($assetRequest->requester_id)?->name;
                     $asset->assigned_to = $requesterName;
                     $asset->assigned_to_id = $assetRequest->requester_id;
                }

                $asset->save();
            }
        }

        // Broadcast event
        event(new AssetRequestUpdated($assetRequest->fresh(), 'status-changed'));

        // Return updated request with asset loaded
        return response()->json($assetRequest->load('asset'));
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

        // Broadcast event
        event(new AssetRequestUpdated($assetRequest->fresh(), 'status-changed'));

        return response()->json($assetRequest);
    }
}
