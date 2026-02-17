<?php

namespace App\Http\Controllers\Api;

use App\Models\Holiday;
use Illuminate\Http\Request;

/**
 * HolidayController - จัดการวันหยุดและวันลา
 * 
 * Extends BaseCrudController + override index + เพิ่ม types/forSlaCalculation
 * รองรับ: วันหยุดราชการ, วันหยุดบริษัท, ลาป่วย, ลาพักร้อน, ลากิจ
 * 
 * Routes:
 * - GET    /api/holidays                  - รายการทั้งหมด (filter type, user_id, date range)
 * - POST   /api/holidays                  - สร้างวันหยุด
 * - PUT    /api/holidays/{id}             - แก้ไข
 * - DELETE /api/holidays/{id}             - ลบ
 * - GET    /api/holidays/types            - ประเภทวันหยุดทั้งหมด
 * - GET    /api/holidays/sla-calculation  - ดึงวันหยุดสำหรับคำนวณ SLA
 */
class HolidayController extends BaseCrudController
{
    protected string $modelClass = Holiday::class;

    protected array $validationRules = [
        'name' => 'required|string|max:255',
        'type' => 'nullable|string|in:public_holiday,company_holiday,sick_leave,annual_leave,personal_leave,other',
        'description' => 'nullable|string',
        'date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:date',
        'is_recurring' => 'nullable|boolean',
        'user_id' => 'nullable|integer|exists:users,id',
    ];

    /**
     * Get all holidays with user relationship
     */
    public function index(Request $request)
    {
        $query = Holiday::with('user');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->forUser($request->user_id);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('date', '<=', $request->to_date);
        }

        // Order by date
        $query->orderBy('date', 'desc');

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * Get holiday types
     */
    public function types()
    {
        return response()->json([
            'success' => true,
            'data' => [
                ['value' => 'public_holiday', 'label' => 'วันหยุดราชการ'],
                ['value' => 'company_holiday', 'label' => 'วันหยุดบริษัท'],
                ['value' => 'sick_leave', 'label' => 'ลาป่วย'],
                ['value' => 'annual_leave', 'label' => 'ลาพักร้อน'],
                ['value' => 'personal_leave', 'label' => 'ลากิจ'],
                ['value' => 'other', 'label' => 'อื่นๆ'],
            ],
        ]);
    }

    /**
     * Get holidays for SLA calculation (only affects_all = true or specific user)
     */
    public function forSlaCalculation(Request $request)
    {
        $userId = $request->user_id;
        
        $query = Holiday::query();
        
        if ($userId) {
            $query->forUser($userId);
        } else {
            $query->affectsAll();
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }
}
