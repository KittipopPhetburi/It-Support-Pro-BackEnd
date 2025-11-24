<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Problem;
use Illuminate\Http\Request;

class ProblemController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status_id' => 'required|exists:problem_statuses,id',
            'owner_id' => 'required|exists:users,id',
        ]);

        $validated['code'] = 'PRB-' . time();
        $problem = Problem::create($validated);

        return response()->json($problem, 201);
    }

    public function attachIncident(Request $request, Problem $problem)
    {
        $validated = $request->validate([
            'incident_id' => 'required|exists:incidents,id',
        ]);

        $problem->incidents()->syncWithoutDetaching([$validated['incident_id']]);

        return response()->json(['message' => 'Incident attached']);
    }

    public function detachIncident(Request $request, Problem $problem)
    {
        $validated = $request->validate([
            'incident_id' => 'required|exists:incidents,id',
        ]);

        $problem->incidents()->detach($validated['incident_id']);

        return response()->json(['message' => 'Incident detached']);
    }
}
