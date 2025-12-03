<?php

namespace App\Http\Controllers\Api;

use App\Models\BusinessHour;
use Illuminate\Http\Request;

class BusinessHourController extends BaseCrudController
{
    protected string $modelClass = BusinessHour::class;

    protected array $validationRules = [
        'day_of_week' => 'required|integer|min:0|max:6',
        'start_time' => 'nullable|date_format:H:i',
        'end_time' => 'nullable|date_format:H:i',
        'is_working_day' => 'required|boolean',
    ];

    public function isOpen()
    {
        $now = now();
        $dayOfWeek = $now->dayOfWeek;
        $currentTime = $now->format('H:i:s');

        $businessHour = BusinessHour::where('day_of_week', $dayOfWeek)->first();

        if (!$businessHour || !$businessHour->is_working_day) {
            return response()->json([
                'is_open' => false,
                'message' => 'Outside business hours',
            ]);
        }

        $isOpen = $currentTime >= $businessHour->start_time && $currentTime <= $businessHour->end_time;

        return response()->json([
            'is_open' => $isOpen,
            'current_time' => $currentTime,
            'business_hours' => $businessHour,
        ]);
    }

    public function getByDay($day)
    {
        $businessHour = BusinessHour::where('day_of_week', $day)->first();

        if (!$businessHour) {
            return response()->json([
                'message' => 'Business hours not found for this day',
            ], 404);
        }

        return response()->json([
            'data' => $businessHour,
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'hours' => 'required|array',
            'hours.*.id' => 'nullable|exists:business_hours,id',
            'hours.*.day_of_week' => 'required|integer|min:0|max:6',
            'hours.*.start_time' => 'nullable|date_format:H:i',
            'hours.*.end_time' => 'nullable|date_format:H:i',
            'hours.*.is_working_day' => 'required|boolean',
        ]);

        $updatedHours = [];

        foreach ($request->hours as $hourData) {
            $businessHour = BusinessHour::updateOrCreate(
                ['day_of_week' => $hourData['day_of_week']],
                [
                    'start_time' => $hourData['start_time'] ?? null,
                    'end_time' => $hourData['end_time'] ?? null,
                    'is_working_day' => $hourData['is_working_day'],
                ]
            );
            $updatedHours[] = $businessHour;
        }

        return response()->json([
            'message' => 'Business hours updated successfully',
            'data' => $updatedHours,
        ]);
    }
}
