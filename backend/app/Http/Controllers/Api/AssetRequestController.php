<?php

namespace App\Http\Controllers\Api;

use App\Models\AssetRequest;

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
