<?php

namespace App\Http\Controllers\Api;

use App\Models\Incident;
use App\Events\IncidentUpdated;
use App\Events\AssetUpdated;
use App\Models\User;
use App\Notifications\IncidentNotification;
use App\Channels\TelegramChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class PublicIncidentController extends BaseCrudController
{
    protected string $modelClass = Incident::class;

    // Override store to allow public access without auth
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'reporter_name' => 'required|string|max:255',
            'reporter_email' => 'nullable|email|max:255',
            'reporter_phone' => 'nullable|string|max:50',
            'asset_id' => 'nullable|string',
            'serial_number' => 'nullable|string|max:255',
            // requester_id ไม่ต้อง validate ตรงนี้ เดี๋ยวเช็คเองข้างล่าง
        ]);

        // Find asset
        $asset = null;
        if (!empty($data['asset_id'])) {
            $asset = \App\Models\Asset::where('id', (int)$data['asset_id'])
                ->orWhere('qr_code', $data['asset_id'])
                ->orWhere('serial_number', $data['asset_id'])
                ->first();
        }

        // Determine Requester
        // 1. If requester_id provided (logged in user) and valid, use it.
        // 2. Else, fallback to default admin/user.
        $requesterId = null;
        if ($request->has('requester_id')) {
            $inputVerify = $request->input('requester_id');
            if (is_numeric($inputVerify) && \App\Models\User::find($inputVerify)) {
                $requesterId = $inputVerify;
            }
        }
        
        if (!$requesterId) {
             $defaultRequester = User::where('role', 'Admin')->first() ?? User::first();
             $requesterId = $defaultRequester?->id;
        }

        // Create Incident
        $incident = Incident::create([
            'title' => $data['title'],
            'description' => $data['description'] . "\n\n---\nผู้แจ้ง: " . $data['reporter_name'] . 
                            "\nอีเมล: " . ($data['reporter_email'] ?? '-') . 
                            "\nเบอร์โทร: " . ($data['reporter_phone'] ?? '-') .
                            ($data['serial_number'] ? "\nSerial Number: " . $data['serial_number'] : ''),
            'priority' => 'Medium',
            'status' => 'Open',
            'category' => 'Hardware',
            'subcategory' => 'แจ้งซ่อม (QR Scan)',
            'requester_id' => $requesterId, // Use default system user
            'asset_id' => $asset?->id,
            'asset_name' => $asset?->name,
            'asset_brand' => $asset?->brand,
            'asset_model' => $asset?->model,
            'asset_serial_number' => $data['serial_number'] ?? $asset?->serial_number,
            'organization' => $asset?->organization ?? 'ไม่ระบุ',
            'contact_phone' => $data['reporter_phone'],
            'is_custom_asset' => false,
        ]);

        // Update Asset Status to Maintenance
        if ($asset) {
            $incident->previous_asset_status = $asset->status;
            $incident->save();
            
            // Only update master status if it's not already Maintenance
            if ($asset->status !== 'Maintenance') {
                 $asset->update(['status' => 'Maintenance']);
                 broadcast(new AssetUpdated($asset->fresh(), 'updated'))->toOthers();
            }
        }

        // Broadcast
        broadcast(new IncidentUpdated($incident, 'created'))->toOthers();

        // Notification
        try {
            Notification::route(TelegramChannel::class, 'system')
                ->notify(new IncidentNotification($incident, 'created'));
        } catch (\Exception $e) {
            \Log::error('Failed to send public incident notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'ส่งคำขอแจ้งซ่อมสำเร็จ',
            'data' => [
                'id' => $incident->id,
                'title' => $incident->title,
                'status' => $incident->status,
            ],
        ], 201);
    }
}
