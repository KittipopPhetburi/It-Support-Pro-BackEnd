<?php

namespace App\Http\Controllers\Api;

use App\Models\Sla;

class SlaController extends BaseCrudController
{
    protected string $modelClass = Sla::class;

    protected array $validationRules = [
        'name' => 'required|string|max:255',
        'priority' => 'required|string|max:50',
        'response_time' => 'required|integer|min:0',
        'resolution_time' => 'required|integer|min:0',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function all()
    {
        return response()->json([
            'data' => Sla::all(),
        ]);
    }

    public function getByPriority($priority)
    {
        $sla = Sla::where('priority', $priority)->where('is_active', true)->first();
        
        if (!$sla) {
            return response()->json([
                'message' => 'SLA not found for this priority',
            ], 404);
        }

        return response()->json([
            'data' => $sla,
        ]);
    }
}
