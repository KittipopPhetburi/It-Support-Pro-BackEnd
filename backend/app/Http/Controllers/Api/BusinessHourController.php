<?php

namespace App\Http\Controllers\Api;

use App\Models\BusinessHour;
use Illuminate\Http\Request;

/**
 * BusinessHourController - จัดการเวลาทำการ (Business Hours)
 * 
 * Extends BaseCrudController + override update + เพิ่ม isOpen/getByDay/bulkUpdate
 * แปลงรูปแบบเวลา (H:i / H:i:s) อัตโนมัติ
 * 
 * Routes:
 * - GET    /api/business-hours              - รายการทั้งหมด (7 วัน)
 * - PUT    /api/business-hours/{id}         - แก้ไข (normalize time format)
 * - GET    /api/business-hours/is-open      - เช็คว่าตอนนี้เปิดอยู่ไหม
 * - GET    /api/business-hours/day/{day}    - ดูเวลาทำการตามวัน (0-6)
 * - PUT    /api/business-hours/bulk-update  - อัปเดตทั้ง 7 วันพร้อมกัน
 */
class BusinessHourController extends BaseCrudController
{
    protected string $modelClass = BusinessHour::class;

    protected array $validationRules = [
        'day_of_week' => 'required|integer|min:0|max:6',
        'start_time' => 'nullable|string',
        'end_time' => 'nullable|string',
        'is_working_day' => 'required|boolean',
    ];

    /**
     * Override update to handle time format conversion
     */
    public function update(Request $request, $id)
    {
        $businessHour = BusinessHour::findOrFail($id);
        
        $data = $request->validate([
            'day_of_week' => 'sometimes|integer|min:0|max:6',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'is_working_day' => 'sometimes|boolean',
        ]);

        // Convert time format if needed (handle both H:i and H:i:s)
        if (isset($data['start_time']) && $data['start_time']) {
            $data['start_time'] = $this->normalizeTimeFormat($data['start_time']);
        }
        if (isset($data['end_time']) && $data['end_time']) {
            $data['end_time'] = $this->normalizeTimeFormat($data['end_time']);
        }

        // If not a working day, clear times
        if (isset($data['is_working_day']) && !$data['is_working_day']) {
            $data['start_time'] = null;
            $data['end_time'] = null;
        }

        $businessHour->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Business hour updated successfully',
            'data' => $businessHour,
        ]);
    }

    /**
     * Normalize time format to H:i
     */
    private function normalizeTimeFormat($time)
    {
        if (!$time) return null;
        
        // If already in H:i or H:i:s format
        if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time)) {
            return substr($time, 0, 5); // Return just H:i
        }
        
        // Try to parse and convert
        try {
            $parsed = \Carbon\Carbon::parse($time);
            return $parsed->format('H:i');
        } catch (\Exception $e) {
            return $time;
        }
    }

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
            'hours.*.start_time' => 'nullable|string',
            'hours.*.end_time' => 'nullable|string',
            'hours.*.is_working_day' => 'required|boolean',
        ]);

        $updatedHours = [];

        foreach ($request->hours as $hourData) {
            // Normalize time format
            $startTime = isset($hourData['start_time']) ? $this->normalizeTimeFormat($hourData['start_time']) : null;
            $endTime = isset($hourData['end_time']) ? $this->normalizeTimeFormat($hourData['end_time']) : null;

            // If not a working day, clear times
            if (!$hourData['is_working_day']) {
                $startTime = null;
                $endTime = null;
            }

            $businessHour = BusinessHour::updateOrCreate(
                ['day_of_week' => $hourData['day_of_week']],
                [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
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
