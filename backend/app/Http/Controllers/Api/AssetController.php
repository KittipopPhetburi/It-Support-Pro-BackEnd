<?php

namespace App\Http\Controllers\Api;

use App\Models\Asset;
use Illuminate\Http\Request;

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
     * Override index to load branch relation for organization display
     * and include available_quantity for status display
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
     * Display the specified asset with maintenance history, borrowing history,
     * and serial statuses for individual serial tracking
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
     * Get maintenance history for a specific asset
     */
    public function maintenanceHistory($id)
    {
        $asset = Asset::findOrFail($id);
        $history = $asset->maintenanceHistories()->with('technician', 'incident')->get();
        return response()->json($history);
    }

    /**
     * Get borrowing history for a specific asset
     */
    public function borrowingHistory($id)
    {
        $asset = Asset::findOrFail($id);
        $history = $asset->borrowingHistories()->with('user', 'processor')->get();
        return response()->json($history);
    }

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
     * Update asset with serial status sync logic
     */
    public function update(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);

        $rules = $this->updateValidationRules ?: $this->validationRules;
        $data = $rules ? $request->validate($rules) : $request->all();

        // Check if status is changing
        if (isset($data['status']) && $data['status'] !== $asset->status) {
            $newStatus = $data['status'];
            
            // Sync status to all serial numbers if they exist
            if (!empty($asset->serial_mapping)) {
                $mapping = $asset->serial_mapping;
                foreach ($mapping as $serial => $info) {
                    // Update status for each serial
                    // If status is 'Available', we should clear any 'assigned_to' info potentially? 
                    // For now, just syncing the status status.
                    $mapping[$serial]['status'] = $newStatus;
                    
                    // Add a note about the mass update
                    $mapping[$serial]['note'] = ($mapping[$serial]['note'] ?? '') . " [System: Bulk status update to $newStatus]";
                }
                $data['serial_mapping'] = $mapping;
            }
        }

        $asset->fill($data);
        $asset->save();

        return response()->json($asset);
    }

    /**
     * Store multiple assets at once (Bulk Create)
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
     * Check if serial numbers exist (for validation before submit)
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

