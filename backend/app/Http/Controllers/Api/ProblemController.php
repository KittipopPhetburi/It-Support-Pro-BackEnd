<?php

namespace App\Http\Controllers\Api;

use App\Models\Problem;

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
    ];
}
