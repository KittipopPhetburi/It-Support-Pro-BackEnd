<?php

namespace App\Http\Controllers\Api;

use App\Models\SatisfactionSurvey;
use Illuminate\Http\Request;

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

    /**
     * Get satisfaction survey by ticket ID
     */
    public function getByTicketId($ticketId)
    {
        $survey = SatisfactionSurvey::where('ticket_id', $ticketId)
            ->with('respondent')
            ->first();

        if (!$survey) {
            return response()->json(['message' => 'Survey not found'], 404);
        }

        return response()->json($survey);
    }

    /**
     * Check if a ticket has been surveyed
     */
    public function checkTicket($ticketId)
    {
        $exists = SatisfactionSurvey::where('ticket_id', $ticketId)->exists();
        
        return response()->json([
            'ticket_id' => $ticketId,
            'has_survey' => $exists,
        ]);
    }

    /**
     * Get statistics for satisfaction surveys
     */
    public function statistics()
    {
        $surveys = SatisfactionSurvey::all();
        
        $totalResponses = $surveys->count();
        $averageRating = $totalResponses > 0 ? round($surveys->avg('rating'), 2) : 0;
        
        $ratingDistribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingDistribution[$i] = $surveys->where('rating', $i)->count();
        }

        return response()->json([
            'total_responses' => $totalResponses,
            'average_rating' => $averageRating,
            'rating_distribution' => $ratingDistribution,
        ]);
    }
}
