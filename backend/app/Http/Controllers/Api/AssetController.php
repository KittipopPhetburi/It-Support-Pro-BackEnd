<?php

namespace App\Http\Controllers\Api;

use App\Models\Asset;
use Illuminate\Http\Request;

/**
 * AssetController - จัดการสินทรัพย์ (Asset Management)
 * 
 * Extends BaseCrudController + override index/show/update + เพิ่ม methods
 * 
 * ความสามารถพิเศษ:
 * - รองรับการติดตามสถานะรายตัว serial (serial_mapping)
 * - Bulk create: สร้างหลาย asset พร้อมกัน + เช็ค serial ซ้ำ
 * - ซิงค์สถานะทั้ง asset เมื่อเปลี่ยน status (อัปเดต serial_mapping ทุกตัว)
 * 
 * Routes:
 * - GET    /api/assets                         - รายการทั้งหมด (พร้อม branch)
 * - GET    /api/assets/{id}                    - รายละเอียด + ประวัติซ่อม/ยืม
 * - POST   /api/assets                         - สร้าง asset
 * - PUT    /api/assets/{id}                    - แก้ไข + sync serial status
 * - DELETE /api/assets/{id}                    - ลบ asset
 * - GET    /api/assets/statistics               - จำนวนตามสถานะ
 * - GET    /api/assets/{id}/maintenance-history  - ประวัติการซ่อม
 * - POST   /api/assets/bulk                     - สร้างหลายรายการ
 * - POST   /api/assets/check-serial-numbers     - เช็ค serial ซ้ำ
 */
class AssetController extends BaseCrudController
{
    protected string $modelClass = Asset::class;

    protected array $validationRules = [
        'name' => 'required|string|max:255',
        'type' => 'required|string|max:255',
        'category' => 'nullable|string|max:255',
        'brand' => 'nullable|string|max:255',
        'model' => 'nullable|string|max:255',
        'serial_number' => 'nullable|string|max:255',
        'inventory_number' => 'nullable|string|max:255',
        'quantity' => 'nullable|integer|min:1',
        'status' => 'required|in:Available,In Use,Maintenance,Retired,On Loan',
        'assigned_to_id' => 'nullable|integer|exists:users,id',
        'assigned_to' => 'nullable|string|max:255',
        'assigned_to_email' => 'nullable|email|max:255',
        'assigned_to_phone' => 'nullable|string|max:50',
        'location' => 'nullable|string|max:255',
        'ip_address' => 'nullable|string|max:45',
        'mac_address' => 'nullable|string|max:17',
        'license_key' => 'nullable|string|max:255',
        'license_type' => 'nullable|string|max:50',
        'purchase_date' => 'nullable|date',
        'start_date' => 'nullable|date',
        'warranty_expiry' => 'nullable|date',
        'expiry_date' => 'nullable|date',
        'total_licenses' => 'nullable|integer',
        'used_licenses' => 'nullable|integer',
        'branch_id' => 'nullable|integer|exists:branches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'department' => 'nullable|string|max:255',
        'organization' => 'nullable|string|max:255',
        'qr_code' => 'nullable|string|max:255',
        'serial_mapping' => 'nullable|array',
    ];

    /**
     * ดึงรายการ assets ทั้งหมด
     * 
     * GET /api/assets
     * โหลด branch relation + รองรับ pagination: ?per_page=20
     * available_quantity จะถูกคำนวณจาก accessor ของ Model
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $query = Asset::with('branch');

        if ($request->has('per_page')) {
            $paginated = $query->paginate((int) $request->get('per_page', 15));
            return $paginated;
        }

        $assets = $query->get();
        return $assets;
    }

    /**
     * ดึงรายละเอียด asset พร้อมประวัติการซ่อมและการยืม
     * 
     * GET /api/assets/{id}
     * โหลด: maintenanceHistories, borrowingHistories + serial statuses
     */
    public function show($id)
    {
        $asset = Asset::with([
            'maintenanceHistories.incident', 
            'maintenanceHistories.technician',
            'borrowingHistories.user',
            'borrowingHistories.processor'
        ])->findOrFail($id);
        
        // Add extra available serials if needed (or could be added to appends)
        $asset->available_serials = $asset->getAvailableSerials();
        
        return response()->json($asset);
    }

    /**
     * Public show - สำหรับแสดงข้อมูล asset สาธารณะ (ไม่ต้อง login)
     * ใช้สำหรับ QR Code scanning
     * ค้นหาได้ด้วย ID, QR Code, หรือ Serial Number
     */
    public function publicShow($identifier)
    {
        // ค้นหาด้วย ID ก่อน
        $asset = Asset::with(['branch', 'maintenanceHistories.technician'])
            ->where('id', $identifier)
            ->orWhere('qr_code', $identifier)
            ->orWhere('serial_number', $identifier)
            ->first();

        if (!$asset) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบอุปกรณ์ในระบบ',
            ], 404);
        }

        // Return limited public information
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $asset->id,
                'name' => $asset->name,
                'type' => $asset->type,
                'category' => $asset->category ?? 'Hardware',
                'brand' => $asset->brand,
                'model' => $asset->model,
                'serial_number' => $asset->serial_number,
                'status' => $asset->status,
                'organization' => $asset->organization ?? $asset->branch?->name,
                'department' => $asset->department,
                'assigned_to' => $asset->assigned_to,
                'qr_code' => $asset->qr_code,
                'purchase_date' => $asset->purchase_date,
                'warranty_expiry' => $asset->warranty_expiry,
                'ip_address' => $asset->ip_address,
                'mac_address' => $asset->mac_address,
                'license_key' => $asset->category === 'Software' ? $asset->license_key : null,
                'license_type' => $asset->license_type,
                'expiry_date' => $asset->expiry_date,
                'maintenance_history' => $asset->maintenanceHistories->take(5)->map(function ($history) {
                    return [
                        'id' => $history->id,
                        'date' => $history->maintenance_date ?? $history->created_at,
                        'description' => $history->description,
                        'technician' => $history->technician?->name,
                        'cost' => $history->cost,
                    ];
                }),
            ],
        ]);
    }

    /**
     * ดึงประวัติการซ่อมของ asset
     * 
     * GET /api/assets/{id}/maintenance-history
     */
    public function maintenanceHistory($id)
    {
        $asset = Asset::findOrFail($id);
        $history = $asset->maintenanceHistories()->with('technician', 'incident')->get();
        return response()->json($history);
    }

    /**
     * ดึงประวัติการยืม/เบิกของ asset
     * 
     * GET /api/assets/{id}/borrowing-history
     */
    public function borrowingHistory($id)
    {
        $asset = Asset::findOrFail($id);
        $history = $asset->borrowingHistories()->with('user', 'processor')->get();
        return response()->json($history);
    }

    /**
     * สถิติรวมของ assets
     * 
     * GET /api/assets/statistics
     * คืนจำนวนตาม status (Available, In Use, On Loan, Maintenance, Retired)
     * และตาม category (Hardware, Software)
     */
    public function statistics()
    {
        return response()->json([
            'total' => Asset::count(),
            'available' => Asset::where('status', 'Available')->count(),
            'in_use' => Asset::where('status', 'In Use')->count(),
            'on_loan' => Asset::where('status', 'On Loan')->count(),
            'maintenance' => Asset::where('status', 'Maintenance')->count(),
            'retired' => Asset::where('status', 'Retired')->count(),
            'hardware' => Asset::where('category', 'Hardware')->count(),
            'software' => Asset::where('category', 'Software')->count(),
        ]);
    }

    /**
     * แก้ไขข้อมูล asset
     * 
     * PUT /api/assets/{id}
     * ถ้า status เปลี่ยน → sync status ไปทุกตัวใน serial_mapping
     * เพื่อให้ทุก serial มีสถานะตรงกัน
     */
    public function update(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);

        $rules = $this->updateValidationRules ?: $this->validationRules;
        $data = $rules ? $request->validate($rules) : $request->all();

        // CASE A: Updating Specific Serial Statuses (via serial_mapping)
        if (isset($data['serial_mapping'])) {
            $mapping = $data['serial_mapping'];
            
            // 1. Update the mapping in JSON
            $currentMapping = $asset->serial_mapping ?? [];
            foreach ($mapping as $serial => $info) {
                // Ensure we keep existing data if partial update
                $currentMapping[$serial] = array_merge($currentMapping[$serial] ?? [], $info);
            }
            $data['serial_mapping'] = $currentMapping;

            // 2. Recalculate Master Status based on all serials
            $allSerials = $asset->getSerialNumbersArray();
            $allStatuses = [];
            
            foreach ($allSerials as $serial) {
                // Check if this serial has a specific status in the mapping
                if (isset($currentMapping[$serial]) && isset($currentMapping[$serial]['status'])) {
                    $allStatuses[] = $currentMapping[$serial]['status'];
                } else {
                    // Default to 'Available' if no mapping
                    $allStatuses[] = 'Available';
                }
            }
            
            // Logic: 
            // - If ANY are 'Available' -> Master is 'Available' (so users can borrow)
            // - If ALL are 'Maintenance' -> Master is 'Maintenance'
            // - If ALL are 'In Use' -> Master is 'In Use' OR 'On Loan'
            // - If Mixed (e.g. In Use + Maintenance) -> 'In Use' (Busy)
            
            if (in_array('Available', $allStatuses)) {
                $data['status'] = 'Available';
            } elseif (in_array('In Use', $allStatuses) && !in_array('Available', $allStatuses)) {
                $data['status'] = 'In Use'; 
            } elseif (count(array_unique($allStatuses)) === 1) {
                 // All same (e.g. all Maintenance, all Retired)
                $data['status'] = $allStatuses[0];
            } else {
                // Fallback for other mixed states (e.g. Retired + Maintenance) -> Maintenance as safest
                $data['status'] = 'Maintenance'; 
            }
        }
        
        // CASE B: Updating Master Status directly (Bulk apply to all serials)
        elseif (isset($data['status']) && $data['status'] !== $asset->status) {
            $newStatus = $data['status'];
            
            // Sync status to all serial numbers if they exist
            if (!empty($asset->serial_mapping)) {
                $mapping = $asset->serial_mapping;
                foreach ($mapping as $serial => $info) {
                    $mapping[$serial]['status'] = $newStatus;
                    $mapping[$serial]['note'] = ($mapping[$serial]['note'] ?? '') . " [Bulk update: $newStatus]";
                }
                $data['serial_mapping'] = $mapping;
            }
        }

        $asset->fill($data);
        $asset->save();

        return response()->json($asset);
    }

    /**
     * สร้างหลาย assets พร้อมกัน (Bulk Create)
     * 
     * POST /api/assets/bulk
     * รับ common_data (ข้อมูลร่วม) + serial_numbers[]
     * - เช็ค serial ซ้ำก่อนสร้าง
     * - สร้าง asset 1 ตัวต่อ 1 serial + QR code
     * - ใช้ transaction เพื่อความถูกต้อง
     * - broadcast AssetUpdated('created') ทุกตัว
     * 
     * @return JsonResponse 201 {success, count, assets} หรือ 422 (ซ้ำ)
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'common_data' => 'required|array',
            'common_data.name' => 'required|string|max:255',
            'common_data.type' => 'required|string|max:255',
            'serial_numbers' => 'required|array|min:1',
            'serial_numbers.*' => 'required|string|max:255',
        ]);

        $commonData = $request->input('common_data');
        $serialNumbers = $request->input('serial_numbers');

        // Sync organization from branch if not provided
        if (!empty($commonData['branch_id']) && empty($commonData['organization'])) {
            $branch = \App\Models\Branch::find($commonData['branch_id']);
            if ($branch) {
                $commonData['organization'] = $branch->name;
            }
        }

        // Check for duplicates in database
        $existingSerials = Asset::whereIn('serial_number', $serialNumbers)
            ->pluck('serial_number')
            ->toArray();

        if (count($existingSerials) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Serial numbers already exist',
                'duplicates' => $existingSerials,
            ], 422);
        }

        // Create assets
        $createdAssets = [];
        \DB::beginTransaction();
        try {
            foreach ($serialNumbers as $index => $serialNumber) {
                $assetData = array_merge($commonData, [
                    'serial_number' => $serialNumber,
                    'qr_code' => 'QR-' . strtoupper(substr(md5($serialNumber . time() . $index), 0, 8)),
                ]);
                $asset = Asset::create($assetData);
                $createdAssets[] = $asset;
            }
            \DB::commit();

            // Load branch relation for all created assets
            $createdAssets = Asset::with('branch')
                ->whereIn('id', array_map(fn($a) => $a->id, $createdAssets))
                ->get()
                ->toArray();

            // Broadcast event for real-time update
            foreach ($createdAssets as $asset) {
                $assetModel = Asset::find($asset['id']);
                if ($assetModel) {
                    broadcast(new \App\Events\AssetUpdated($assetModel, 'created'));
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'สร้างอุปกรณ์สำเร็จ ' . count($createdAssets) . ' รายการ',
                'count' => count($createdAssets),
                'assets' => $createdAssets,
            ], 201);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการสร้างอุปกรณ์',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * เช็ค serial numbers ซ้ำก่อน submit
     * 
     * POST /api/assets/check-serial-numbers
     * รับ serial_numbers[] แล้วคืนรายการที่ซ้ำ
     */
    public function checkSerialNumbers(Request $request)
    {
        $serialNumbers = $request->input('serial_numbers', []);
        
        $existingSerials = Asset::whereIn('serial_number', $serialNumbers)
            ->pluck('serial_number')
            ->toArray();

        return response()->json([
            'duplicates' => $existingSerials,
        ]);
    }
}

