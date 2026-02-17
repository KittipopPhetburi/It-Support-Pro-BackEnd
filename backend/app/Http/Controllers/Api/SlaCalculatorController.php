<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Sla;
use App\Services\SlaCalculatorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SlaCalculatorController - คำนวณ SLA (SLA Calculator)
 * 
 * ไม่ extends BaseCrudController - ใช้ Controller ตรง
 * ใช้ SlaCalculatorService สำหรับคำนวณเวลา business hours
 * 
 * ความสามารถ:
 * - คำนวณ SLA status ของ incident (met/breached/warning)
 * - คำนวณ business minutes ระหว่าง 2 timestamps
 * - คำนวณ deadline จาก priority
 * - เช็คว่าอยู่ใน business hours หรือไม่
 * 
 * Routes:
 * - GET    /api/sla/calculate/{incidentId}        - คำนวณ SLA ของ incident
 * - POST   /api/sla/calculate                      - คำนวณ SLA จาก custom params
 * - POST   /api/sla/business-minutes               - คำนวณ business minutes
 * - POST   /api/sla/deadline                        - คำนวณ deadline
 * - GET    /api/sla/is-business-hours               - เช็ค business hours
 * - GET    /api/sla/open-incidents                   - SLA summary ของ open incidents ทั้งหมด
 */
class SlaCalculatorController extends Controller
{
    protected SlaCalculatorService $slaCalculator;

    public function __construct(SlaCalculatorService $slaCalculator)
    {
        $this->slaCalculator = $slaCalculator;
    }

    /**
     * Calculate SLA status for a specific incident
     */
    public function calculateForIncident(Request $request, $incidentId): JsonResponse
    {
        $incident = Incident::findOrFail($incidentId);
        
        // Get SLA based on priority
        $sla = Sla::where('priority', $incident->priority)
            ->where('is_active', true)
            ->first();

        if (!$sla) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบ SLA สำหรับ Priority: ' . $incident->priority,
            ], 404);
        }

        $startTime = Carbon::parse($incident->created_at);
        
        // Calculate response SLA
        $responseSlaMinutes = $sla->response_time; // ใน API response_time เป็นนาที
        $responseStatus = $this->slaCalculator->getSlaStatus($startTime, $responseSlaMinutes);

        // Calculate resolution SLA
        $resolutionSlaMinutes = $sla->resolution_time; // ใน API resolution_time เป็นนาที
        $resolutionStatus = $this->slaCalculator->getSlaStatus($startTime, $resolutionSlaMinutes);

        return response()->json([
            'success' => true,
            'data' => [
                'incident_id' => $incident->id,
                'priority' => $incident->priority,
                'status' => $incident->status,
                'created_at' => $startTime->toIso8601String(),
                'sla' => [
                    'name' => $sla->name,
                    'response' => [
                        'sla_minutes' => $responseSlaMinutes,
                        'sla_formatted' => $this->slaCalculator->formatMinutes($responseSlaMinutes),
                        ...$responseStatus,
                        'remaining_formatted' => $this->slaCalculator->formatMinutes($responseStatus['remaining_minutes']),
                    ],
                    'resolution' => [
                        'sla_minutes' => $resolutionSlaMinutes,
                        'sla_formatted' => $this->slaCalculator->formatMinutes($resolutionSlaMinutes),
                        ...$resolutionStatus,
                        'remaining_formatted' => $this->slaCalculator->formatMinutes($resolutionStatus['remaining_minutes']),
                    ],
                ],
            ],
        ]);
    }

    /**
     * Calculate SLA for custom parameters
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_time' => 'required|date',
            'sla_minutes' => 'required|integer|min:1',
            'current_time' => 'nullable|date',
        ]);

        $startTime = Carbon::parse($validated['start_time']);
        $slaMinutes = $validated['sla_minutes'];
        $currentTime = isset($validated['current_time']) 
            ? Carbon::parse($validated['current_time']) 
            : Carbon::now();

        $status = $this->slaCalculator->getSlaStatus($startTime, $slaMinutes, $currentTime);

        return response()->json([
            'success' => true,
            'data' => [
                'start_time' => $startTime->toIso8601String(),
                'current_time' => $currentTime->toIso8601String(),
                'sla_minutes' => $slaMinutes,
                'sla_formatted' => $this->slaCalculator->formatMinutes($slaMinutes),
                ...$status,
                'remaining_formatted' => $this->slaCalculator->formatMinutes($status['remaining_minutes']),
            ],
        ]);
    }

    /**
     * Calculate business minutes between two times
     */
    public function calculateBusinessMinutes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date',
        ]);

        $startTime = Carbon::parse($validated['start_time']);
        $endTime = Carbon::parse($validated['end_time']);

        $businessMinutes = $this->slaCalculator->calculateBusinessMinutes($startTime, $endTime);

        return response()->json([
            'success' => true,
            'data' => [
                'start_time' => $startTime->toIso8601String(),
                'end_time' => $endTime->toIso8601String(),
                'business_minutes' => $businessMinutes,
                'business_hours' => round($businessMinutes / 60, 2),
                'formatted' => $this->slaCalculator->formatMinutes($businessMinutes),
                'total_calendar_minutes' => $startTime->diffInMinutes($endTime),
            ],
        ]);
    }

    /**
     * Get SLA deadline
     */
    public function getDeadline(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_time' => 'required|date',
            'sla_minutes' => 'required|integer|min:1',
        ]);

        $startTime = Carbon::parse($validated['start_time']);
        $slaMinutes = $validated['sla_minutes'];

        $deadline = $this->slaCalculator->calculateSlaDeadline($startTime, $slaMinutes);

        return response()->json([
            'success' => true,
            'data' => [
                'start_time' => $startTime->toIso8601String(),
                'sla_minutes' => $slaMinutes,
                'sla_formatted' => $this->slaCalculator->formatMinutes($slaMinutes),
                'deadline' => $deadline->toIso8601String(),
                'deadline_thai' => $deadline->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Check if current time is within business hours
     */
    public function isWithinBusinessHours(Request $request): JsonResponse
    {
        $time = $request->has('time') 
            ? Carbon::parse($request->input('time')) 
            : Carbon::now();

        $isWithin = $this->slaCalculator->isWithinBusinessHours($time);

        return response()->json([
            'success' => true,
            'data' => [
                'time' => $time->toIso8601String(),
                'is_within_business_hours' => $isWithin,
                'day_of_week' => $time->dayOfWeek,
                'day_name' => $time->locale('th')->dayName,
            ],
        ]);
    }

    /**
     * Get SLA summary for all open incidents
     */
    public function getOpenIncidentsSlaStatus(): JsonResponse
    {
        $openIncidents = Incident::whereNotIn('status', ['Resolved', 'Closed'])
            ->with('assignee')
            ->get();

        $summary = [
            'total' => $openIncidents->count(),
            'breached' => 0,
            'at_risk' => 0,
            'warning' => 0,
            'on_track' => 0,
        ];

        $incidents = $openIncidents->map(function ($incident) use (&$summary) {
            $sla = Sla::where('priority', $incident->priority)
                ->where('is_active', true)
                ->first();

            if (!$sla) {
                return null;
            }

            $startTime = Carbon::parse($incident->created_at);
            $resolutionStatus = $this->slaCalculator->getSlaStatus(
                $startTime, 
                $sla->resolution_time
            );

            $summary[$resolutionStatus['status']]++;

            return [
                'id' => $incident->id,
                'title' => $incident->title,
                'priority' => $incident->priority,
                'status' => $incident->status,
                'assignee' => $incident->assignee?->name,
                'created_at' => $startTime->toIso8601String(),
                'sla_status' => $resolutionStatus['status'],
                'remaining_minutes' => $resolutionStatus['remaining_minutes'],
                'remaining_formatted' => $this->slaCalculator->formatMinutes($resolutionStatus['remaining_minutes']),
                'percentage_used' => $resolutionStatus['percentage_used'],
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'incidents' => $incidents,
            ],
        ]);
    }
}
