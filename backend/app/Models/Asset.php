<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory, HasBranch;

    protected $fillable = [
        'name',
        'type',
        'category',
        'brand',
        'model',
        'serial_number',
        'inventory_number',
        'quantity',
        'status',
        'assigned_to_id',
        'assigned_to',
        'assigned_to_email',
        'assigned_to_phone',
        'location',
        'ip_address',
        'mac_address',
        'license_key',
        'license_type',
        'purchase_date',
        'start_date',
        'warranty_expiry',
        'expiry_date',
        'total_licenses',
        'used_licenses',
        'branch_id',
        'department_id',
        'department',
        'organization',
        'qr_code',
        'serial_mapping', // Added for individual serial status
    ];

    protected $appends = [
        'serial_statuses',
        'available_quantity',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'start_date' => 'date',
        'warranty_expiry' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'integer',
        'total_licenses' => 'integer',
        'quantity' => 'integer',
        'total_licenses' => 'integer',
        'used_licenses' => 'integer',
        'serial_mapping' => 'array', // Cast JSON to array
    ];

    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function departmentRelation()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    public function maintenanceHistories()
    {
        return $this->hasMany(MaintenanceHistory::class)->orderBy('created_at', 'desc');
    }

    public function borrowingHistories()
    {
        return $this->hasMany(BorrowingHistory::class)->orderBy('action_date', 'desc');
    }

    /**
     * Get asset requests for this asset
     */
    public function assetRequests()
    {
        return $this->hasMany(AssetRequest::class);
    }

    /**
     * Get all serial numbers as array
     */
    public function getSerialNumbersArray(): array
    {
        if (empty($this->serial_number)) {
            return [];
        }
        // Split by comma, newline (actual), carriage return, space, or literal "\n" sequence
        // We replace literal "\n" with actual newline first, then split
        $normalized = str_replace(['\n', '\r'], "\n", $this->serial_number);
        return preg_split('/[,\n\r\s]+/', $normalized, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Get list of borrowed/withdrawn serials from approved requests
     */
    public function getBorrowedSerials(): array
    {
        return $this->assetRequests()
            ->whereIn('status', ['Approved', 'Received'])
            ->whereNotNull('borrowed_serial')
            ->pluck('borrowed_serial')
            ->toArray();
    }

    /**
     * Get serial statuses with requester info (Accessor: serial_statuses)
     */
    public function getSerialStatusesAttribute(): array
    {
        $allSerials = $this->getSerialNumbersArray();
        $requests = $this->assetRequests()
            ->whereIn('status', ['Approved', 'Received'])
            ->whereNotNull('borrowed_serial')
            ->with('requester')
            ->get()
            ->keyBy('borrowed_serial');

        $statuses = [];
        $mapping = $this->serial_mapping ?? []; // Get custom mapping

        foreach ($allSerials as $serial) {
            // Priority 1: Check if currently borrowed/withdrawn (Active Request)
            if (isset($requests[$serial])) {
                $request = $requests[$serial];
                $statuses[] = [
                    'serial_number' => $serial,
                    'status' => $request->request_type === 'borrow' ? 'On Loan' : 'Withdrawn',
                    'request_id' => $request->id,
                    'request_type' => $request->request_type,
                    'requester_name' => $request->requester_name ?? $request->requester?->name ?? 'Unknown',
                    'requester_id' => $request->requester_id,
                    'approved_at' => $request->approved_at,
                ];
            } 
            // Priority 2: Check custom mapping (Maintenance, Retired, etc.)
            elseif (isset($mapping[$serial]) && isset($mapping[$serial]['status'])) {
                 $statuses[] = [
                    'serial_number' => $serial,
                    'status' => $mapping[$serial]['status'], // e.g., 'Maintenance', 'Retired'
                    'note' => $mapping[$serial]['note'] ?? null,
                    'request_id' => null,
                    'request_type' => null,
                    'requester_name' => null,
                    'requester_id' => null,
                    'approved_at' => null,
                ];
            }
            // Priority 3: Default Available
            else {
                $statuses[] = [
                    'serial_number' => $serial,
                    'status' => 'Available',
                    'request_id' => null,
                    'request_type' => null,
                    'requester_name' => null,
                    'requester_id' => null,
                    'approved_at' => null,
                ];
            }
        }
        return $statuses;
    }

    /**
     * Get available serials (not borrowed/withdrawn)
     */
    public function getAvailableSerials(): array
    {
        $allSerials = $this->getSerialNumbersArray();
        $borrowedSerials = $this->getBorrowedSerials();
        
        // Also filter out serials that are unavailable due to mapping (Maintenance, Retired, etc.)
        $mapping = $this->serial_mapping ?? [];
        $unavailableFromMapping = [];
        foreach ($mapping as $serial => $data) {
            if (isset($data['status']) && $data['status'] !== 'Available') {
                $unavailableFromMapping[] = $serial;
            }
        }
        
        $unavailable = array_merge($borrowedSerials, $unavailableFromMapping);
        
        return array_values(array_diff($allSerials, $unavailable));
    }

    /**
     * Get first available serial
     */
    public function getFirstAvailableSerial(): ?string
    {
        $available = $this->getAvailableSerials();
        return $available[0] ?? null;
    }

    /**
     * Get available quantity (Accessor: available_quantity)
     */
    public function getAvailableQuantityAttribute(): int
    {
        // If we have serial numbers/license keys, count them (Hardware OR Software with keys)
        if (!empty($this->serial_number)) {
             return count($this->getAvailableSerials());
        }

        // Fallback for assets without keys (e.g. old software or consumables)
        if ($this->category === 'Software') {
            return max(0, ($this->total_licenses ?? 0) - ($this->used_licenses ?? 0));
        }

        // Default fallback (though hardware should always have serials ideally)
        return max(0, ($this->quantity ?? 0) - ($this->used_licenses ?? 0));
    }
}
