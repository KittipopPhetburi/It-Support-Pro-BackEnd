<?php

namespace App\Http\Controllers\Api;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * ServiceRequestController - จัดการคำขอบริการ (Service Request)
 * 
 * Extends BaseCrudController + override index/show + เพิ่ม approve/reject/startProgress/complete
 * 
 * Flow: Pending → Approved → In Progress → Completed
 *                ↘ Rejected
 * 
 * Routes:
 * - GET    /api/service-requests                     - รายการทั้งหมด (+ service, requester, approvedBy)
 * - GET    /api/service-requests/{id}                - รายละเอียด
 * - POST   /api/service-requests                     - สร้างคำขอ
 * - PUT    /api/service-requests/{id}                - แก้ไข
 * - POST   /api/service-requests/{id}/approve        - อนุมัติ
 * - POST   /api/service-requests/{id}/reject         - ปฏิเสธ (ต้องมี reason)
 * - POST   /api/service-requests/{id}/start-progress - เริ่มดำเนินการ
 * - POST   /api/service-requests/{id}/complete       - เสร็จสิ้น
 */
class ServiceRequestController extends BaseCrudController
{
    protected string $modelClass = ServiceRequest::class;

    protected array $validationRules = [
        'service_id' => 'required|integer|exists:service_catalog_items,id',
        'service_name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'requester_id' => 'required|integer|exists:users,id',
        'requested_by' => 'nullable|string|max:255',
        'status' => 'required|in:Pending,Approved,In Progress,Completed,Rejected',
        'approved_by_id' => 'nullable|integer|exists:users,id',
        'approved_at' => 'nullable|date',
        'rejected_reason' => 'nullable|string',
        'request_date' => 'nullable|date',
        'completion_date' => 'nullable|date',

        'branch_id' => 'nullable|integer|exists:branches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'organization' => 'nullable|string|max:255',
    ];

    public function index(Request $request)
    {
        $query = ServiceRequest::with(['service', 'requester', 'approvedBy']);

        if ($request->has('per_page')) {
            return $query->paginate((int) $request->get('per_page', 15));
        }

        return $query->get();
    }

    public function show($id)
    {
        return ServiceRequest::with(['service', 'requester', 'approvedBy'])->findOrFail($id);
    }

    /**
     * Update request status to Approved
     */
    public function approve(Request $request, $id)
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        
        // ดึง user จาก authenticated request
        $user = $request->user();
        
        // Debug log
        Log::info('Approve request', [
            'service_request_id' => $id,
            'authenticated_user_id' => $user ? $user->id : 'null',
            'authenticated_user_name' => $user ? $user->name : 'null',
        ]);
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        $serviceRequest->status = 'Approved';
        $serviceRequest->approved_by_id = $user->id;
        $serviceRequest->approved_at = now();
        $serviceRequest->save();

        return response()->json($serviceRequest->load(['service', 'requester', 'approvedBy']));
    }

    /**
     * Update request status to Rejected
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string']);
        
        $serviceRequest = ServiceRequest::findOrFail($id);
        
        $serviceRequest->status = 'Rejected';
        $serviceRequest->rejected_reason = $request->reason;
        $serviceRequest->save();

        return response()->json($serviceRequest->load(['service', 'requester', 'approvedBy']));
    }

    /**
     * Start working on request
     */
    public function startProgress($id)
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        $serviceRequest->status = 'In Progress';
        $serviceRequest->save();

        return response()->json($serviceRequest->load(['service', 'requester', 'approvedBy']));
    }

    /**
     * Complete request
     */
    public function complete($id)
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        $serviceRequest->status = 'Completed';
        $serviceRequest->completion_date = now();
        $serviceRequest->save();

        return response()->json($serviceRequest->load(['service', 'requester', 'approvedBy']));
    }
}
