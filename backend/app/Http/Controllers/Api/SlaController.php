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
    ];
}
