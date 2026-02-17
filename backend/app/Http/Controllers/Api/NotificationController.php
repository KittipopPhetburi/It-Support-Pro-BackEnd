<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;

/**
 * NotificationController - จัดการการแจ้งเตือน (Notification CRUD)
 * 
 * Extends BaseCrudController โดยไม่มี override
 * ใช้ CRUD จาก BaseCrudController ตรง
 * 
 * Routes:
 * - GET    /api/notifications           - รายการทั้งหมด
 * - GET    /api/notifications/{id}      - รายละเอียด
 * - POST   /api/notifications           - สร้าง
 * - PUT    /api/notifications/{id}      - แก้ไข
 * - DELETE /api/notifications/{id}      - ลบ
 */
class NotificationController extends BaseCrudController
{
    protected string $modelClass = Notification::class;

    protected array $validationRules = [
        'user_id' => 'required|integer|exists:users,id',
        'type' => 'required|string|max:255',
        'message' => 'required|string',
        'read' => 'nullable|boolean',
    ];
}
