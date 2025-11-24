<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    public function index()
    {
        $incidents = Incident::with(['category', 'priority', 'status', 'requester', 'assignee'])->paginate(10);
        return response()->json($incidents);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'incident_category_id' => 'required|exists:incident_categories,id',
            'priority_id' => 'required|exists:incident_priorities,id',
            'status_id' => 'required|exists:incident_statuses,id',
            'requester_id' => 'required|exists:users,id',
        ]);

        // Generate code
        $validated['code'] = 'INC-' . time(); // Simple code generation

        $incident = Incident::create($validated);

        return response()->json($incident, 201);
    }

    public function show(Incident $incident)
    {
        $incident->load(['category', 'priority', 'status', 'requester', 'assignee', 'logs', 'attachments']);
        return response()->json($incident);
    }

    public function update(Request $request, Incident $incident)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'incident_category_id' => 'sometimes|exists:incident_categories,id',
            'priority_id' => 'sometimes|exists:incident_priorities,id',
            'status_id' => 'sometimes|exists:incident_statuses,id',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $incident->update($validated);

        return response()->json($incident);
    }

    public function destroy(Incident $incident)
    {
        $incident->delete();
        return response()->json(null, 204);
    }
}
