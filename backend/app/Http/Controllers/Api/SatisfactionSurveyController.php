<?php

namespace App\Http\Controllers\Api;

use App\Models\SatisfactionSurvey;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Store a new satisfaction survey and auto-close the incident
     */
    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules);
        
        // Create the survey
        $survey = SatisfactionSurvey::create($data);
        
        // Auto-close the incident after user submits satisfaction survey
        $incidentId = $data['ticket_id'];
        $incident = Incident::find($incidentId);
        
        if ($incident && $incident->status === 'Resolved') {
            $incident->status = 'Closed';
            $incident->closed_at = now();
            $incident->satisfaction_rating = $data['rating'];
            $incident->satisfaction_comment = $data['feedback'] ?? null;
            $incident->satisfaction_date = now();
            $incident->save();
        }
        
        return response()->json($survey, 201);
    }

    /**
     * Get pending satisfaction surveys for current user
     * Returns resolved incidents that the user created but hasn't rated yet
     */
    public function pending()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Get resolved incidents created by the user that don't have a survey yet
        $pendingSurveys = Incident::where('requester_id', $user->id)
            ->where('status', 'Resolved')
            ->whereDoesntHave('satisfactionSurvey')
            ->with(['assignee'])
            ->orderBy('resolved_at', 'desc')
            ->get()
            ->map(function ($incident) {
                return [
                    'incident_id' => $incident->id,
                    'ticket_id' => 'INC-' . str_pad($incident->id, 5, '0', STR_PAD_LEFT),
                    'title' => $incident->title,
                    'description' => $incident->description,
                    'resolved_at' => $incident->resolved_at,
                    'technician_name' => $incident->assignee?->name ?? 'ไม่ระบุ',
                    'technician_id' => $incident->assignee_id,
                    'category' => $incident->category,
                ];
            });

        return response()->json([
            'pending_count' => $pendingSurveys->count(),
            'surveys' => $pendingSurveys,
        ]);
    }

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
