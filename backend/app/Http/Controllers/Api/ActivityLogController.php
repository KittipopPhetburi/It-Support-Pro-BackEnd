<?php

namespace App\Http\Controllers\Api;

use App\Models\ActivityLog;

class ActivityLogController extends BaseCrudController
{
    protected string $modelClass = ActivityLog::class;

    protected array $validationRules = [
        'user_id' => 'nullable|integer|exists:users,id',
        'action' => 'required|string|max:255',
        'module' => 'required|string|max:255',
        'timestamp' => 'nullable|date',
        'details' => 'nullable|string',
    ];
}
