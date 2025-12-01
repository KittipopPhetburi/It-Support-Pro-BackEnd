<?php

namespace App\Http\Controllers\Api;

use App\Models\SatisfactionSurvey;

class SatisfactionSurveyController extends BaseCrudController
{
    protected string $modelClass = SatisfactionSurvey::class;

    protected array $validationRules = [
        'ticket_id' => 'required|string|max:255',
        'rating' => 'required|integer|min:1|max:5',
        'feedback' => 'nullable|string',
        'respondent_id' => 'required|integer|exists:users,id',
        'submitted_at' => 'nullable|date',
    ];
}
