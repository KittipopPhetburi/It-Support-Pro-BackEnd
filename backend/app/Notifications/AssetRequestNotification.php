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
        $channels = [TelegramChannel::class, \App\Channels\OrganizationMailChannel::class];

        // Only save to database if the notifiable is a valid model (User)
        if ($notifiable instanceof \Illuminate\Database\Eloquent\Model) {
            $channels[] = 'database';
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        $req = $this->assetRequest;
        $title = $this->getTitle($this->type);
        $statusEmoji = $this->getStatusEmoji($this->type);
        $requestTypeLabel = $this->getRequestTypeLabel($req->request_type);
        
        $mail = (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject("[{$title}] #{$req->ticket_id} - {$requestTypeLabel}")
            ->greeting("{$statusEmoji} {$title}")
            ->line("**เลขที่คำขอ:** #{$req->ticket_id}")
            ->line("**ประเภทคำขอ:** {$requestTypeLabel}")
            ->line("**รายการ:** " . ($req->asset ? $req->asset->name : ($req->asset_type ?: 'ไม่ระบุ')))
            ->line("**ผู้ขอ:** {$req->requester_name}")
            ->line("**แผนก:** " . ($req->department ?? 'ไม่ระบุ'))
            ->line("**สถานะ:** {$title}");

        // แสดงเหตุผลในการขอ
        $reason = $req->reason ?? $req->justification ?? null;
        if ($reason) {
            $mail->line("**เหตุผลในการขอ:** {$reason}");
        }

        // แสดงเหตุผลที่ปฏิเสธ
        if ($this->type === 'rejected' && $req->reject_reason) {
            $mail->line("**เหตุผลที่ปฏิเสธ:** {$req->reject_reason}");
        }

        // แสดงผู้อนุมัติ
        if ($this->type === 'approved' && $req->approved_by) {
            $mail->line("**ผู้อนุมัติ:** {$req->approved_by}");
        }

        $mail->line("📅 " . now()->setTimezone('Asia/Bangkok')->format('d/m/Y H:i'));
        $mail->salutation("ขอบคุณที่ใช้บริการ IT Support Pro");

        return $mail;
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

        // แสดงเหตุผลในการขอ
        $reason = $req->reason ?? $req->justification ?? null;
        if ($reason) {
            $message .= "<b>เหตุผล:</b> {$reason}\n";
        }
        
        if ($this->type === 'rejected' && $req->reject_reason) {
            $message .= "<b>เหตุผลที่ปฏิเสธ:</b> {$req->reject_reason}\n";
        }

        if ($this->type === 'approved') {
            $message .= "<b>ผู้อนุมัติ:</b> {$req->approved_by}\n";
        }

        $message .= "\n📅 " . now()->setTimezone('Asia/Bangkok')->format('d/m/Y H:i');

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
