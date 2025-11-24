<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\IncidentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        $query = Incident::query();

        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->has('assignee_id')) {
            $query->where('assignee_id', $request->assignee_id);
        }
        
        if ($request->has('requester_id')) {
            $query->where('requester_id', $request->requester_id);
        }

        return response()->json($query->with(['status', 'priority', 'category', 'assignee'])->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'service_id' => 'required|exists:services,id',
            'incident_category_id' => 'required|exists:incident_categories,id',
            'priority_id' => 'required|exists:incident_priorities,id',
            'status_id' => 'required|exists:incident_statuses,id',
            'requester_id' => 'required|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'contact_name' => 'nullable',
            'contact_phone' => 'nullable',
            'source' => 'required',
        ]);

        // Generate Code (Simple implementation)
        $validated['code'] = 'INC-' . time();
        $validated['opened_at'] = now();

        $incident = Incident::create($validated);

        return response()->json($incident, 201);
    }

    public function show(Incident $incident)
    {
        return response()->json($incident->load(['status', 'priority', 'category', 'assignee', 'requester', 'logs', 'attachments']));
    }

    public function update(Request $request, Incident $incident)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required',
            'description' => 'sometimes|required',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $incident->update($validated);
        return response()->json($incident);
    }

    public function updateStatus(Request $request, Incident $incident)
    {
        $validated = $request->validate([
            'status_id' => 'required|exists:incident_statuses,id',
            'message' => 'required|string',
        ]);

        $oldStatusId = $incident->status_id;
        $newStatusId = $validated['status_id'];

        DB::transaction(function () use ($incident, $oldStatusId, $newStatusId, $validated, $request) {
            $incident->update(['status_id' => $newStatusId]);

            IncidentLog::create([
                'incident_id' => $incident->id,
                'user_id' => $request->user()->id ?? 1, // Fallback if no auth
                'log_type' => 'status_change',
                'old_status_id' => $oldStatusId,
                'new_status_id' => $newStatusId,
                'message' => $validated['message'],
                'created_at' => now(),
            ]);
        });

        return response()->json($incident->fresh('status'));
    }
}
