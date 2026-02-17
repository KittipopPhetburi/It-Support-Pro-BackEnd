<?php

namespace App\Http\Controllers\Api;

use App\Models\ServiceCatalogItem;

/**
 * ServiceCatalogItemController - จัดการรายการแค็ตตาล็อกบริการ (Service Catalog)
 * 
 * Extends BaseCrudController โดยไม่มี override
 * ใช้ CRUD จาก BaseCrudController ตรง
 * 
 * Routes:
 * - GET    /api/service-catalog-items           - รายการทั้งหมด
 * - GET    /api/service-catalog-items/{id}      - รายละเอียด
 * - POST   /api/service-catalog-items           - สร้าง
 * - PUT    /api/service-catalog-items/{id}      - แก้ไข
 * - DELETE /api/service-catalog-items/{id}      - ลบ
 */
class ServiceCatalogItemController extends BaseCrudController
{
    protected string $modelClass = ServiceCatalogItem::class;

    protected array $validationRules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'category' => 'nullable|string|max:255',
        'sla' => 'nullable|string|max:255',
        'cost' => 'nullable|numeric',
        'icon' => 'nullable|string|max:255',
        'estimated_time' => 'nullable|string|max:255',
    ];
}
