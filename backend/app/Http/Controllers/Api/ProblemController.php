<?php

namespace App\Http\Controllers\Api;

use App\Models\Problem;
use Illuminate\Http\Request;

class ProblemController extends BaseCrudController
{
    protected string $modelClass = Problem::class;

    protected array $validationRules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'status' => 'required|in:Open,Investigating,Known Error,Resolved,Closed',
        'priority' => 'required|in:Low,Medium,High,Critical',
        'assigned_to_id' => 'nullable|integer|exists:users,id',
        'root_cause' => 'nullable|string',
        'workaround' => 'nullable|string',
        'solution' => 'nullable|string',
        'resolved_at' => 'nullable|date',
        'related_incidents' => 'nullable|array',
        'related_incidents.*' => 'integer|exists:incidents,id',
    ];

    public function index(Request $request)
    {
        $query = Problem::with(['assignedTo', 'incidents']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        
        $problems = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json(['success' => true, 'data' => $problems]);
    }

    public function show($id)
    {
        $problem = Problem::with(['assignedTo', 'incidents'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $problem]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules);
        
        $relatedIncidents = $request->input('related_incidents', []);
        unset($validated['related_incidents']);
        
        $problem = Problem::create($validated);
        
        if (!empty($relatedIncidents)) {
            $problem->incidents()->sync($relatedIncidents);
        }
        
        $problem->load(['assignedTo', 'incidents']);
        
        return response()->json(['success' => true, 'data' => $problem, 'message' => 'Problem created successfully'], 201);
    }

    public function update(Request $request, $id)
    {
        $problem = Problem::findOrFail($id);
        
        $rules = $this->validationRules;
        $rules['title'] = 'sometimes|required|string|max:255';
        $rules['status'] = 'sometimes|required|in:Open,Investigating,Known Error,Resolved,Closed';
        $rules['priority'] = 'sometimes|required|in:Low,Medium,High,Critical';
        
        $validated = $request->validate($rules);
        
        $relatedIncidents = $request->input('related_incidents');
        unset($validated['related_incidents']);
        
        $problem->update($validated);
        
        if ($relatedIncidents !== null) {
            $problem->incidents()->sync($relatedIncidents);
        }
        
        $problem->load(['assignedTo', 'incidents']);
        
        return response()->json(['success' => true, 'data' => $problem, 'message' => 'Problem updated successfully']);
    }
}
