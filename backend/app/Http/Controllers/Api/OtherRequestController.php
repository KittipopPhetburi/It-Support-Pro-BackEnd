<?php

namespace App\Http\Controllers\Api;

use App\Models\OtherRequest;
use App\Models\Asset;
use App\Models\AssetRequest;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OtherRequestNotification;

/**
 * OtherRequestController - จัดการคำขออื่นๆ (Other Requests - จัดซื้อสาธารณูปโภค)
 * 
 * Extends BaseCrudController + override index/store + เพิ่ม approve/reject/complete/receive
 * 
 * Flow: Pending → Approved → Completed (จัดหาแล้ว) → Received (รับของแล้ว)
 *                ↘ Rejected
 * 
 * receive: จัดสรร serial numbers ให้ asset + อัปเดต quantity + serial_mapping
 * Notifications: OtherRequestNotification (Telegram + Database)
 * 
 * Routes:
 * - GET    /api/other-requests                    - รายการทั้งหมด
 * - POST   /api/other-requests                    - สร้างคำขอ + ส่ง Notification
 * - POST   /api/other-requests/{id}/approve       - อนุมัติ
 * - POST   /api/other-requests/{id}/reject        - ปฏิเสธ
 * - POST   /api/other-requests/{id}/complete      - จัดหาเรียบร้อย
 * - POST   /api/other-requests/{id}/receive       - รับของ + จัดสรร serial
 */
class OtherRequestController extends BaseCrudController
{
    protected string $modelClass = OtherRequest::class;

    protected array $validationRules = [
        'requester_id' => 'nullable|integer|exists:users,id',
        'requester_name' => 'nullable|string|max:255',
        'title' => 'required|string|max:255',
        'item_name' => 'nullable|string|max:255',
        'item_type' => 'nullable|string|max:255',
        'request_type' => 'nullable|string|in:Requisition,Borrow,Replace',
        'quantity' => 'nullable|integer|min:1',
        'unit' => 'nullable|string|max:50',
        'description' => 'nullable|string',
        'reason' => 'nullable|string',
        'category' => 'nullable|string|max:255',
        'status' => 'required|in:Pending,Approved,In Progress,Completed,Received,Rejected',
        'request_date' => 'nullable|date',
        'branch_id' => 'nullable|integer|exists:branches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'organization' => 'nullable|string|max:255',
        'department' => 'nullable|string|max:255',
        'approved_by' => 'nullable|string|max:255',
        'approved_at' => 'nullable|date',
        'rejected_by' => 'nullable|string|max:255',
        'rejected_at' => 'nullable|date',
        'reject_reason' => 'nullable|string',
        'completed_by' => 'nullable|string|max:255',
        'completed_at' => 'nullable|date',
        'received_at' => 'nullable|date',
        'brand' => 'nullable|string|max:255',
        'model' => 'nullable|string|max:255',
        'asset_id' => 'nullable|integer|exists:assets,id',
    ];

    /**
     * Override index to eager load relationships and support limit
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $query = OtherRequest::with(['requester', 'branch', 'department', 'asset'])
            ->orderBy('created_at', 'desc');

        // Filter by user role if needed (optional)

        // Support pagination
        if ($request->has('per_page')) {
            return $query->paginate((int) $request->get('per_page'));
        }

        // Support simple limit
        if ($request->has('limit')) {
            $limit = (int) $request->get('limit');
            if ($limit > 0) {
                $query->limit($limit);
            }
        }

        return $query->get();
    }

    /**
     * Create other request
     */
    public function store(\Illuminate\Http\Request $request)
    {
        // 1. Validate
        $data = $request->validate($this->validationRules);

        // 2. Create
        $otherRequest = OtherRequest::create($data);

        // 3. Send Notification
        try {
            Notification::route(\App\Channels\TelegramChannel::class, 'system')
                ->notifyNow(new OtherRequestNotification($otherRequest, 'created'));
        } catch (\Exception $e) {
            \Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return response()->json($otherRequest, 201);
    }

    /**
     * Approve other request
     */
    public function approve($id)
    {
        $otherRequest = OtherRequest::findOrFail($id);
        
        $otherRequest->update([
            'status' => 'Approved',
            'approved_by' => auth()->user()->name ?? 'System',
            'approved_at' => now(),
        ]);

        // Send Notification
        // Send Notification
        try {
            Notification::route(\App\Channels\TelegramChannel::class, 'system')
                ->notifyNow(new OtherRequestNotification($otherRequest, 'approved'));
        } catch (\Exception $e) {
            \Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Request approved successfully',
            'data' => $otherRequest->fresh()
        ]);
    }

    /**
     * Reject other request
     */
    public function reject(\Illuminate\Http\Request $request, $id)
    {
        $otherRequest = OtherRequest::findOrFail($id);
        
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $otherRequest->update([
            'status' => 'Rejected',
            'rejected_by' => auth()->user()->name ?? 'System',
            'rejected_at' => now(),
            'reject_reason' => $request->reason,
        ]);

        // Send Notification
        // Send Notification
        try {
            Notification::route(\App\Channels\TelegramChannel::class, 'system')
                ->notifyNow(new OtherRequestNotification($otherRequest, 'rejected'));
        } catch (\Exception $e) {
            \Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Request rejected successfully',
            'data' => $otherRequest->fresh()
        ]);
    }

    /**
     * Complete other request (จัดหาเรียบร้อย)
     */
    public function complete(\Illuminate\Http\Request $request, $id)
    {
        $otherRequest = OtherRequest::findOrFail($id);
        
        $request->validate([
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
        ]);

        $otherRequest->update([
            'status' => 'Completed',
            'completed_by' => auth()->user()->name ?? 'System',
            'completed_at' => now(),
            'brand' => $request->brand,
            'model' => $request->model,
        ]);

        // Send Notification
        // Send Notification
        try {
            Notification::route(\App\Channels\TelegramChannel::class, 'system')
                ->notifyNow(new OtherRequestNotification($otherRequest, 'completed'));
        } catch (\Exception $e) {
            \Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Request completed successfully',
            'data' => $otherRequest->fresh()
        ]);
    }

    /**
     * Receive other request (รับของแล้ว) - Also assigns serial numbers and updates asset
     */
    public function receive($id)
    {
        $otherRequest = OtherRequest::findOrFail($id);
        
        // Update request status
        $otherRequest->update([
            'status' => 'Received',
            'received_at' => now(),
        ]);

        $assignedSerials = [];

        // Assign serial numbers if asset_id exists
        $asset = null;

        // Try to find asset by ID first
        if ($otherRequest->asset_id) {
            $asset = Asset::find($otherRequest->asset_id);
        }
        
        // If not found by ID, try to find by name and branch (fallback)
        if (!$asset && ($otherRequest->item_name || $otherRequest->title)) {
            $query = Asset::where('name', $otherRequest->item_name ?? $otherRequest->title);
            
            // If request has branch, try to match asset in same branch
            if ($otherRequest->branch_id) {
                // If we also have a branch match, prioritize it. 
                // But simplified: Just add the condition if we want strict branch matching
                // Or maybe just get the first matching name to be safe? 
                // Let's try strict branch match first, if not found then maybe loose?
                // For now, let's keep it simple: Filter by branch if possible.
                $branchQuery = clone $query;
                 $assetWithBranch = $branchQuery->where('branch', function($q) use ($otherRequest) {
                     $q->select('name')->from('branches')->where('id', $otherRequest->branch_id);
                 })->first();
                 
                 // Alternative: if asset stores branch name string
                 if (!$assetWithBranch) {
                      // Try matching simplified text branch? Or just ignore branch for now to ensure deduction works
                 }
                 $asset = $assetWithBranch;
            }
            
            // If still not found (or no branch logic), just take the first asset with that name
            if (!$asset) {
                $asset = Asset::where('name', $otherRequest->item_name ?? $otherRequest->title)->first();
            }
        }

        if ($asset) {
                // Get available serials
                $availableSerials = $asset->getAvailableSerials();
                
                // Case 1: Has Serial Numbers to assign
                if (count($availableSerials) > 0) {
                    $quantityNeeded = min($otherRequest->quantity, count($availableSerials));
                    
                    // Create AssetRequest records to track serial assignments
                    for ($i = 0; $i < $quantityNeeded; $i++) {
                        $serial = $availableSerials[$i] ?? null;
                        if ($serial) {
                            AssetRequest::create([
                                'asset_id' => $asset->id,
                                'asset_type' => $asset->type ?? $asset->category ?? 'Other',
                                'requester_id' => $otherRequest->requester_id,
                                'requester_name' => $otherRequest->requester_name,
                                'request_type' => 'withdraw', // เบิกออก
                                'status' => 'Received',
                                'borrowed_serial' => $serial,
                                'branch_id' => $otherRequest->branch_id,
                                'department_id' => $otherRequest->department_id,
                                'quantity' => 1,
                                'approved_by' => auth()->user()->name ?? 'System',
                                'approved_at' => now(),
                                'reason' => 'เบิกจาก OtherRequest: ' . ($otherRequest->title ?? $otherRequest->item_name ?? 'Unknown'),
                            ]);
                            $assignedSerials[] = $serial;
                        }
                    }
                } 
                // Case 2: No Serial Numbers (Non-serialized/Consumables)
                else {
                    $qty = $otherRequest->quantity;
                    
                    // Decrement quantity directly
                    if ($asset->quantity >= $qty) {
                         $asset->decrement('quantity', $qty);
                    } else {
                         // Force decrement or handle insufficient stock (here we just decrement/zero out)
                         $asset->update(['quantity' => max(0, $asset->quantity - $qty)]);
                    }

                    // Create history record without serial number
                    AssetRequest::create([
                        'asset_id' => $asset->id,
                        'asset_type' => $asset->type ?? $asset->category ?? 'Other',
                        'requester_id' => $otherRequest->requester_id,
                        'requester_name' => $otherRequest->requester_name,
                        'request_type' => $otherRequest->request_type === 'Borrow' ? 'borrow' : 'withdraw',
                        'status' => 'Received',
                        'borrowed_serial' => null,
                        'branch_id' => $otherRequest->branch_id,
                        'department_id' => $otherRequest->department_id,
                        'quantity' => $qty,
                        'approved_by' => auth()->user()->name ?? 'System',
                        'approved_at' => now(),
                        'reason' => 'เบิกจาก OtherRequest: ' . ($otherRequest->title ?? $otherRequest->item_name ?? 'Unknown'),
                    ]);
                }
            }

        // Send Notification
        try {
             Notification::route(\App\Channels\TelegramChannel::class, 'system')
                ->notifyNow(new OtherRequestNotification($otherRequest, 'received'));
        } catch (\Exception $e) {
             \Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Request received successfully',
            'data' => $otherRequest->fresh(),
            'assigned_serials' => $assignedSerials,
        ]);
    }
}