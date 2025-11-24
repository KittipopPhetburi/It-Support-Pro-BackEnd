<?php

namespace App\Http\Controllers;

use App\Models\OtherRequest;
use Illuminate\Http\Request;

class OtherRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = OtherRequest::query();

        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->has('requester_id')) {
            $query->where('requester_id', $request->requester_id);
        }

        return response()->json($query->with(['status', 'category', 'requester'])->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'category_id' => 'required|exists:other_request_categories,id',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'requested_date' => 'nullable|date',
        ]);

        // Default status: 1 (New)
        $statusId = 1;

        $otherRequest = OtherRequest::create([
            'code' => 'OR-' . time(),
            'requester_id' => $request->user()->id ?? 1,
            'branch_id' => $validated['branch_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'status_id' => $statusId,
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'requested_date' => $validated['requested_date'] ?? now(),
        ]);

        return response()->json($otherRequest, 201);
    }

    public function show(OtherRequest $otherRequest)
    {
        return response()->json($otherRequest->load(['status', 'category', 'requester', 'handler']));
    }

    public function update(Request $request, OtherRequest $otherRequest)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required',
            'description' => 'nullable',
            'status_id' => 'sometimes|exists:other_request_statuses,id',
            'handler_id' => 'nullable|exists:users,id',
        ]);

        $otherRequest->update($validated);
        return response()->json($otherRequest);
    }
}
