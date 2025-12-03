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
     * Get all satisfaction surveys with relationships
     */
    public function index(Request $request)
    {
        $query = SatisfactionSurvey::with(['respondent', 'incident.assignee']);

        if ($request->has('per_page')) {
            return $query->orderBy('submitted_at', 'desc')->paginate((int) $request->get('per_page', 15));
        }

        return $query->orderBy('submitted_at', 'desc')->get();
    }

    /**
     * Get satisfaction survey by ticket ID
     */
    public function getByTicketId($ticketId)
    {
        $survey = SatisfactionSurvey::where('ticket_id', $ticketId)
            ->with(['respondent', 'incident.assignee'])
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
