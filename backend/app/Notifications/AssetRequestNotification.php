<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Channels\TelegramChannel;
use App\Models\AssetRequest;

class AssetRequestNotification extends Notification
{
    // Removed ShouldQueue to send synchronously for immediate feedback
    // use Queueable; 

    public $assetRequest;
    protected $type; // 'created', 'approved', 'rejected', 'received'

    public function __construct(AssetRequest $assetRequest, string $type = 'created')
    {
        $this->assetRequest = $assetRequest;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        $channels = [TelegramChannel::class];

        // Only save to database if the notifiable is a valid model (User)
        if ($notifiable instanceof \Illuminate\Database\Eloquent\Model) {
            $channels[] = 'database';
        }

        return $channels;
    }

    public function toTelegram($notifiable)
    {
        $req = $this->assetRequest;
        $statusEmoji = $this->getStatusEmoji($this->type);
        $title = $this->getTitle($this->type);
        $requestTypeLabel = $this->getRequestTypeLabel($req->request_type);

        $message = "<b>{$statusEmoji} {$title}</b>\n\n";
        $message .= "<b>Ticket:</b> #{$req->ticket_id}\n";
        $message .= "<b>ประเภท:</b> {$requestTypeLabel}\n";
        $message .= "<b>ผู้ขอ:</b> {$req->requester_name}\n";
        $message .= "<b>แผนก:</b> " . ($req->department ?? 'ไม่ระบุ') . "\n";
        
        // Item detail varies by asset type
        $itemDetail = $req->asset_id && $req->asset 
            ? "{$req->asset->name} ({$req->asset->type})" 
            : ($req->asset_type ?: 'ไม่ระบุ');
            
        $message .= "<b>รายการ:</b> {$itemDetail}\n";
        
        if ($this->type === 'rejected' && $req->reject_reason) {
            $message .= "<b>เหตุผลที่ปฏิเสธ:</b> {$req->reject_reason}\n";
        }

        if ($this->type === 'approved') {
            $message .= "<b>ผู้อนุมัติ:</b> {$req->approved_by}\n";
        }

        $message .= "\n📅 " . now()->format('d/m/Y H:i');

        return $message;
    }

    public function toArray($notifiable)
    {
        return [
            'request_id' => $this->assetRequest->id,
            'ticket_id' => $this->assetRequest->ticket_id,
            'title' => $this->assetRequest->request_type,
            'status' => $this->type,
            'message' => "Request #{$this->assetRequest->ticket_id} was {$this->type}",
        ];
    }

    private function getStatusEmoji($type)
    {
        return match ($type) {
            'created' => '📝',
            'approved' => '✅',
            'rejected' => '❌',
            'received' => '📦',
            default => '🔔',
        };
    }

    private function getTitle($type)
    {
        return match ($type) {
            'created' => 'ทำรายการใหม่ (New Request)',
            'approved' => 'รายการได้รับการอนุมัติ (Approved)',
            'rejected' => 'รายการถูกปฏิเสธ (Rejected)',
            'received' => 'ยืนยันรับของแล้ว (Item Received)',
            default => 'อัปเดตสถานะคำขอ',
        };
    }

    private function getRequestTypeLabel($type)
    {
        return match ($type) {
            'Requisition' => 'เบิก (Requisition)',
            'Borrow' => 'ยืม (Borrow)',
            'Replace' => 'ทดแทน (Replace)',
            default => $type,
        };
    }
}
