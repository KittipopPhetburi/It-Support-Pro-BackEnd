<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProblemController extends Controller
{
    public function index(Request $request)
    {
        $query = Problem::query();

        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->has('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        return response()->json($query->with(['status', 'owner'])->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'status_id' => 'required|exists:problem_statuses,id',
            'owner_id' => 'required|exists:users,id',
            'root_cause' => 'nullable',
            'workaround' => 'nullable',
            'permanent_fix' => 'nullable',
        ]);

        $validated['code'] = 'PRB-' . time();

        $problem = Problem::create($validated);

        return response()->json($problem, 201);
    }

    public function show(Problem $problem)
    {
        return response()->json($problem->load(['status', 'owner', 'incidents']));
    }

    public function update(Request $request, Problem $problem)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required',
            'description' => 'sometimes|required',
            'status_id' => 'sometimes|exists:problem_statuses,id',
            'owner_id' => 'sometimes|exists:users,id',
            'root_cause' => 'nullable',
            'workaround' => 'nullable',
            'permanent_fix' => 'nullable',
        ]);

        $problem->update($validated);
        return response()->json($problem);
    }

    public function attachIncident(Request $request, Problem $problem)
    {
        $validated = $request->validate([
            'incident_id' => 'required|exists:incidents,id',
        ]);

        $problem->incidents()->syncWithoutDetaching([$validated['incident_id']]);

        return response()->json($problem->load('incidents'));
    }

    public function detachIncident(Request $request, Problem $problem)
    {
        $validated = $request->validate([
            'incident_id' => 'required|exists:incidents,id',
        ]);

        $problem->incidents()->detach($validated['incident_id']);

        return response()->json($problem->load('incidents'));
    }
}
