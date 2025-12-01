<?php

namespace App\Http\Controllers\Api;

use App\Models\Holiday;

class HolidayController extends BaseCrudController
{
    protected string $modelClass = Holiday::class;

    protected array $validationRules = [
        'name' => 'required|string|max:255',
        'date' => 'required|date',
        'recurring' => 'required|boolean',
    ];
}
