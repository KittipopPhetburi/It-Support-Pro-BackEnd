<?php

namespace App\Http\Controllers\Api;

use App\Models\Sla;

/**
 * SlaController - จัดการ SLA (Service Level Agreement)
 * 
 * Extends BaseCrudController + เพิ่ม all/getByPriority
 * กำหนด response_time และ resolution_time ตาม priority
 * 
 * Routes:
 * - GET    /api/slas              - รายการทั้งหมด (CRUD)
 * - POST   /api/slas              - สร้าง
 * - PUT    /api/slas/{id}         - แก้ไข
 * - DELETE /api/slas/{id}         - ลบ
 * - GET    /api/slas/all          - ดึงทั้งหมด (ไม่ paginate)
 * - GET    /api/slas/priority/{priority} - ดึงตาม priority (เฉพาะ active)
 */
class SlaController extends BaseCrudController
{
    protected string $modelClass = Sla::class;

    protected array $validationRules = [
        'name' => 'required|string|max:255',
        'priority' => 'required|string|max:50',
        'response_time' => 'required|integer|min:0',
        'resolution_time' => 'required|integer|min:0',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function all()
    {
        return response()->json([
            'data' => Sla::all(),
        ]);
    }

    public function getByPriority($priority)
    {
        $sla = Sla::where('priority', $priority)->where('is_active', true)->first();
        
        if (!$sla) {
            return response()->json([
                'message' => 'SLA not found for this priority',
            ], 404);
        }

        return response()->json([
            'data' => $sla,
        ]);
    }
}
