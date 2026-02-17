<?php

namespace App\Http\Controllers\Api;

use App\Models\AssetRequest;
use App\Events\AssetRequestUpdated;

/**
 * AssetRequestController - จัดการคำขอเบิก/ยืม/ทดแทนสินทรัพย์
 * 
 * Extends BaseCrudController + override index/show/store/update + เพิ่ม approve/reject
 * 
 * กระบวนการหลัก:
 * - store: สร้างคำขอ + ส่ง AssetRequestNotification
 * - approve: อนุมัติ → จัดสรร serial (FIFO) + เปลี่ยน asset status + สร้าง BorrowingHistory + ส่ง Notification
 * - reject: ปฏิเสธ + บันทึกเหตุผล + ส่ง Notification
 * - update (return): คืนสินทรัพย์ → อัปเดต serial_mapping ตามสภาพ (Normal/Damaged/Lost) + คืน asset status
 * 
 * การจัดการ Serial เมื่อคืน:
 * - Normal → ลบ serial จาก mapping (กลับเป็น Available)
 * - Damaged → เปลี่ยน serial เป็น Maintenance
 * - Lost → เปลี่ยน serial เป็น Lost + ลดจำนวน
 * 
 * Broadcasting: AssetRequestUpdated
 * Notifications: AssetRequestNotification (Telegram + Database)
 * 
 * Routes:
 * - GET    /api/asset-requests                    - รายการทั้งหมด
 * - GET    /api/asset-requests/{id}               - รายละเอียด
 * - POST   /api/asset-requests                    - สร้างคำขอ
 * - PUT    /api/asset-requests/{id}               - อัปเดต/คืนสินทรัพย์
 * - DELETE /api/asset-requests/{id}               - ลบ
 * - GET    /api/asset-requests/statistics          - สถิติ
 * - GET    /api/asset-requests/my                  - คำขอของฉัน
 * - POST   /api/asset-requests/{id}/approve        - อนุมัติ
 * - POST   /api/asset-requests/{id}/reject         - ปฏิเสธ
 */
class AssetRequestController extends BaseCrudController
{
    protected string $modelClass = AssetRequest::class;

    protected array $validationRules = [
        'requester_id' => 'nullable|integer',
        'requester_name' => 'nullable|string|max:255',
        'request_type' => 'nullable|string|in:Requisition,Borrow,Replace',
        'asset_type' => 'nullable|string|max:255', // Changed to nullable for partial updates
        'asset_id' => 'nullable|integer',
        'quantity' => 'nullable|integer|min:1',
        'justification' => 'nullable|string',
        'reason' => 'nullable|string',
        'status' => 'nullable|in:Pending,Approved,Rejected,Fulfilled,Received,Cancelled,Returned',
        'request_date' => 'nullable|date',
        'branch_id' => 'nullable|integer',
        'department_id' => 'nullable|integer',
        'department' => 'nullable|string|max:255',
        'organization' => 'nullable|string|max:255',
        'approved_at' => 'nullable|date',
        'approved_by' => 'nullable|string|max:255',
        'rejected_at' => 'nullable|date',
        'rejected_by' => 'nullable|string|max:255',
        'reject_reason' => 'nullable|string',
        'received_at' => 'nullable|date',
        'due_date' => 'nullable|date',
        // Return fields
        'is_returned' => 'nullable|boolean',
        'return_date' => 'nullable|date',
        'return_condition' => 'nullable|string|in:Normal,Damaged,Lost',
        'return_notes' => 'nullable|string',
    ];

    /**
     * ดึงรายการคำขอทั้งหมด
     * 
     * GET /api/asset-requests
     * โหลด requester, asset, branch, department
     * เรียงตาม created_at ล่าสุด + รองรับ pagination
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $query = AssetRequest::with('requester', 'asset', 'branch', 'departmentRelation')
            ->orderBy('created_at', 'desc');

        // Support pagination
        if ($request->has('per_page')) {
            $requests = $query->paginate((int) $request->get('per_page', 15));
            return response()->json($requests);
        }

        $requests = $query->get();
        return response()->json(['data' => $requests]);
    }

    public function show($id)
    {
        $request = AssetRequest::with('requester', 'asset', 'branch', 'departmentRelation')
            ->findOrFail($id);

        return response()->json($request);
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $data = $this->validationRules
            ? $request->validate($this->validationRules)
            : $request->all();

        $model = AssetRequest::create($data);
        
        // Reload with relationships
        $model = AssetRequest::with('requester', 'asset', 'branch', 'departmentRelation')
            ->findOrFail($model->id);

        // Broadcast event
        event(new AssetRequestUpdated($model, 'created'));

        // Send Notification
        try {
            \Illuminate\Support\Facades\Notification::route(\App\Channels\TelegramChannel::class, 'system')
                ->notify(new \App\Notifications\AssetRequestNotification($model, 'created'));
        } catch (\Exception $e) {
            \Log::error('Failed to send asset request notification: ' . $e->getMessage());
        }

        return response()->json($model, 201);
    }

    public function update(\Illuminate\Http\Request $request, $id)
    {
        $model = AssetRequest::findOrFail($id);

        $rules = $this->updateValidationRules ?: $this->validationRules;
        $rules['status'] = 'nullable|in:Pending,Approved,Rejected,Fulfilled,Received,Cancelled,Returned'; // Update rule here too

        $data = $request->validate($rules);

        // HANDLE ASSET RETURN
        if (isset($data['return_condition']) && !$model->is_returned) {
            $data['is_returned'] = true;
            $data['return_date'] = $data['return_date'] ?? now();
            $data['status'] = 'Returned'; // Change status to release the asset from "Borrowed" list

            if ($model->asset_id) {
                $asset = \App\Models\Asset::find($model->asset_id);
                if ($asset) {
                    $condition = $data['return_condition'];
                    
                    // Case: Serialized Asset
                    if ($model->borrowed_serial) {
                        $mapping = $asset->serial_mapping ?? [];
                        
                        if ($condition === 'Damaged') {
                            // Map to Maintenance (Repairing)
                            $mapping[$model->borrowed_serial] = [
                                'status' => 'Maintenance',
                                'note' => "Damaged returned from Request #{$model->id}",
                                'date' => now()->toDateString()
                            ];
                        } elseif ($condition === 'Lost') {
                            // Map to Lost
                            $mapping[$model->borrowed_serial] = [
                                'status' => 'Lost',
                                'note' => "Lost in Request #{$model->id}",
                                'date' => now()->toDateString()
                            ];
                        } else {
                            // Normal: Ensure no mapping (Available)
                            unset($mapping[$model->borrowed_serial]);
                        }
                        
                        $asset->serial_mapping = $mapping;
                        $asset->save();
                    } 
                    // Case: Non-Serialized Asset
                    else {
                        // Logic for non-serialized (quantity based)
                         if ($condition === 'Damaged') {
                            // Logic: It was used, now returned broken.
                            // Decrement 'used_licenses' (so it's not "used" anymore)
                            // But do NOT increment 'quantity' (available)? 
                            // Actually used_licenses tracks "currently borrowed".
                            // If returned, used_licenses--.
                            // But if broken, we should decrement total quantity?
                            
                            if ($asset->category === 'Software') {
                                $asset->decrement('used_licenses');
                                // Maybe decrement total if lost? But for damaged software?? Software doesn't break physically.
                                // Assuming Hardware Non-Serial (e.g. Mouse)
                            } else {
                                // User returned it.
                                $asset->decrement('used_licenses'); // It's back from user.
                                // But it's broken. So remove from stock.
                                $asset->decrement('quantity');
                            }
                        } elseif ($condition === 'Lost') {
                            // User lost it.
                            $asset->decrement('used_licenses'); // Back from user (conceptually)
                            $asset->decrement('quantity'); // Removed from stock
                        } else {
                            // Normal
                            $asset->decrement('used_licenses'); // Back in stock
                        }
                    }

                    // Prepare asset for status update check
                    // We need to reload/refresh calculated attributes like available_quantity
                    $asset = $asset->fresh();
                    $availableQty = $asset->available_quantity;

                    if ($availableQty > 0) {
                        // If we have stock, it's Available (even if some are borrowed/maintenance)
                        if ($asset->status !== 'Available') {
                            $asset->status = 'Available';
                            $asset->save();
                        }
                    } else {
                        // If stock is 0, check why
                        $borrowedCount = count($asset->getBorrowedSerials());
                        
                        // Check logic for Remaining items
                        if ($borrowedCount > 0) {
                            // Still have borrowed items -> In Use / On Loan
                            // We don't change it if it is already In Use/On Loan
                            if ($asset->status === 'Available') {
                                $asset->status = 'In Use'; // Fallback
                                $asset->save();
                            }
                        } else {
                            // None borrowed. Must be Maintenance, Lost, or Retired.
                            // Check mapping for Maintenance
                            $mapping = $asset->serial_mapping ?? [];
                            $hasMaintenance = false;
                            foreach ($mapping as $m) {
                                if (isset($m['status']) && $m['status'] === 'Maintenance') {
                                    $hasMaintenance = true;
                                    break;
                                }
                            }
                            
                            if ($hasMaintenance) {
                                $asset->status = 'Maintenance';
                                $asset->save();
                            } else {
                                // Default to Retired or keep as is if all Lost
                                // If status was In Use, switch to Retired/Empty
                                if ($asset->status === 'In Use' || $asset->status === 'On Loan') {
                                    $asset->status = 'Retired'; // Or just leave it? 'Retired' makes sense if broken/lost.
                                    $asset->save();
                                }
                            }
                        }
                    }
                }
            }
            
            // Update BorrowingHistory
            $history = \App\Models\BorrowingHistory::where('request_id', $model->id)
                ->where('status', 'active')
                ->first();
                
            if ($history) {
                $history->update([
                    'actual_return_date' => $data['return_date'],
                    'status' => 'returned',
                    'notes' => $history->notes . "\n[Returned: {$data['return_condition']}] " . ($data['return_notes'] ?? ''),
                ]);
            }
        }

        $model->fill($data);
        $model->save();

        // Reload with relationships
        $model = AssetRequest::with('requester', 'asset', 'branch', 'departmentRelation')
            ->findOrFail($model->id);

        // Broadcast event
        event(new AssetRequestUpdated($model, 'updated'));
// ...

        // Check for "Received" status change
        if (isset($data['status']) && $data['status'] === 'Received') {
             try {
                \Illuminate\Support\Facades\Notification::route(\App\Channels\TelegramChannel::class, 'system')
                    ->notify(new \App\Notifications\AssetRequestNotification($model, 'received'));
            } catch (\Exception $e) {
                \Log::error('Failed to send asset received notification: ' . $e->getMessage());
            }
        }

        return response()->json($model);
    }

    public function statistics()
    {
        return response()->json([
            'total' => AssetRequest::count(),
            'pending' => AssetRequest::where('status', 'Pending')->count(),
            'approved' => AssetRequest::where('status', 'Approved')->count(),
            'rejected' => AssetRequest::where('status', 'Rejected')->count(),
            'fulfilled' => AssetRequest::where('status', 'Fulfilled')->count(),
            'received' => AssetRequest::where('status', 'Received')->count(),
        ]);
    }

    public function myRequests()
    {
        $user = auth()->user();
        $requests = AssetRequest::with('requester', 'asset', 'branch', 'departmentRelation')
            ->where('requester_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $requests->getCollection()->transform(function ($request) {
            return $this->addRequesterName($request);
        });

        return response()->json($requests);
    }

    public function approve(AssetRequest $assetRequest)
    {
        // Update Asset status and assigned user if an asset is linked to this request
        $borrowedSerial = null;
        
        if ($assetRequest->asset_id) {
            $asset = \App\Models\Asset::find($assetRequest->asset_id);
            if ($asset) {
                // Check if Asset has specific serial numbers/license keys to manage
                $hasSerials = !empty($asset->serial_number);

                if (!$hasSerials && $asset->category === 'Software') {
                    // Logic for Software WITHOUT specific license keys (Count based)
                    $availableLicenses = ($asset->total_licenses ?? 0) - ($asset->used_licenses ?? 0);
                    if ($availableLicenses <= 0) {
                        return response()->json([
                            'error' => 'License สำหรับซอฟต์แวร์นี้หมดแล้ว (No available licenses)',
                        ], 422);
                    }
                    // Increment used licenses
                    $asset->used_licenses = ($asset->used_licenses ?? 0) + 1;
                    
                } else {
                    // Logic for Assets WITH serials/keys (Hardware OR Software with keys)
                    // Get first available serial (FIFO)
                    $borrowedSerial = $asset->getFirstAvailableSerial();
                    
                    if (!$borrowedSerial) {
                        return response()->json([
                            'error' => 'ไม่มี Serial Number/License Key ที่ว่างสำหรับทรัพย์สินนี้ (No available items)',
                        ], 422);
                    }
                    
                }

                // Get requester info from the request or the related user
                $requesterName = $assetRequest->requester_name;
                $requesterEmail = null;
                $requesterPhone = null;

                // Try to get email and phone from the requester user if exists
                if ($assetRequest->requester_id) {
                    $requester = \App\Models\User::find($assetRequest->requester_id);
                    if ($requester) {
                        $requesterName = $requesterName ?: $requester->name;
                        $requesterEmail = $requester->email;
                        $requesterPhone = $requester->phone ?? null;
                    }
                }

                // Check if this will use the last available serial
                $availableAfterThis = $asset->available_quantity - 1;
                
                // Only update asset status if all serials are now used
                if ($availableAfterThis <= 0) {
                    // Use 'On Loan' for Borrow, 'In Use' for Requisition/Replace
                    $newStatus = $assetRequest->request_type === 'Borrow' ? 'On Loan' : 'In Use';
                    $asset->update([
                        'status' => $newStatus,
                        'assigned_to' => $requesterName,
                        'assigned_to_email' => $requesterEmail,
                        'assigned_to_phone' => $requesterPhone,
                        'assigned_to_id' => $assetRequest->requester_id,
                    ]);
                }
                // If still has available serials, keep status as Available
            }
        }

        // Update request with borrowed serial and borrow date
        $updateData = [
            'status' => 'Approved',
            'approved_at' => now(),
            'approved_by' => auth()->user()->name,
            'borrowed_serial' => $borrowedSerial,
        ];

        // Set borrow_date for Borrow type requests
        if ($assetRequest->request_type === 'Borrow') {
            $updateData['borrow_date'] = now();
        }

        $assetRequest->update($updateData);

        // Create BorrowingHistory record
        if ($assetRequest->asset_id && $borrowedSerial) {
            \App\Models\BorrowingHistory::create([
                'asset_id' => $assetRequest->asset_id,
                'user_id' => $assetRequest->requester_id,
                'user_name' => $assetRequest->requester_name ?? ($assetRequest->requester ? $assetRequest->requester->name : null),
                'action_type' => strtolower($assetRequest->request_type), // borrow, requisition
                'request_id' => $assetRequest->id,
                'action_date' => now(),
                'expected_return_date' => $assetRequest->request_type === 'Borrow' ? now()->addDays(7) : null,
                'notes' => "Serial: {$borrowedSerial} - " . ($assetRequest->justification ?? $assetRequest->reason ?? 'อนุมัติคำขอ'),
                'status' => 'active',
                'processed_by' => auth()->user()->id,
            ]);
        }

        // Broadcast event
        event(new AssetRequestUpdated($assetRequest->fresh(), 'status-changed'));

        // Send Notification
        try {
            \Illuminate\Support\Facades\Notification::route(\App\Channels\TelegramChannel::class, 'system')
                ->notify(new \App\Notifications\AssetRequestNotification($assetRequest, 'approved'));
        } catch (\Exception $e) {
            \Log::error('Failed to send asset approved notification: ' . $e->getMessage());
        }

        // Return updated request with asset loaded
        return response()->json($assetRequest->load('asset'));
    }

    public function reject(AssetRequest $assetRequest)
    {
        $reason = request('reason', '');
        $assetRequest->update([
            'status' => 'Rejected',
            'rejected_at' => now(),
            'rejected_by' => auth()->user()->name,
            'reject_reason' => $reason,
        ]);

        // Broadcast event
        event(new AssetRequestUpdated($assetRequest->fresh(), 'status-changed'));

        // Send Notification
        try {
            \Illuminate\Support\Facades\Notification::route(\App\Channels\TelegramChannel::class, 'system')
                ->notify(new \App\Notifications\AssetRequestNotification($assetRequest, 'rejected'));
        } catch (\Exception $e) {
            \Log::error('Failed to send asset rejected notification: ' . $e->getMessage());
        }

        return response()->json($assetRequest);
    }
}
