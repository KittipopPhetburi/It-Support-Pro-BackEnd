<?php

namespace App\Http\Controllers\Api;

use App\Models\BusinessHour;

class BusinessHourController extends BaseCrudController
{
    protected string $modelClass = BusinessHour::class;

    protected array $validationRules = [
        'day_of_week' => 'required|string|max:20',
        'start_time' => 'nullable|date_format:H:i:s',
        'end_time' => 'nullable|date_format:H:i:s',
        'is_working_day' => 'required|boolean',
    ];
}
