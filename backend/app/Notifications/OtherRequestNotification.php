<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Channels\TelegramChannel;
use App\Models\OtherRequest;

class OtherRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $otherRequest;
    protected $type; // 'created', 'approved', 'rejected', 'completed', 'received'

    public function __construct(OtherRequest $otherRequest, string $type = 'created')
    {
        $this->otherRequest = $otherRequest;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return [TelegramChannel::class, 'database'];
    }

    public function toTelegram($notifiable)
    {
        $req = $this->otherRequest;
        $statusEmoji = $this->getStatusEmoji($this->type);
        $title = $this->getTitle($this->type);

        $message = "<b>{$statusEmoji} {$title}</b>\n\n";
        $message .= "<b>เลขที่:</b> #REQ-{$req->id}\n";
        $message .= "<b>เรื่อง:</b> {$req->title}\n";
        $message .= "<b>ผู้ขอ:</b> {$req->requester_name}\n";
        $message .= "<b>แผนก:</b> {$req->department}\n";
        $message .= "<b>รายการ:</b> {$req->item_name} (x{$req->quantity} {$req->unit})\n";
        
        if ($this->type === 'rejected' && $req->reject_reason) {
            $message .= "<b>เหตุผลที่ปฏิเสธ:</b> {$req->reject_reason}\n";
        }

        $message .= "\n📅 " . now()->format('d/m/Y H:i');

        return $message;
    }

    public function toArray($notifiable)
    {
        return [
            'request_id' => $this->otherRequest->id,
            'title' => $this->otherRequest->title,
            'status' => $this->type,
            'message' => "Request #{$this->otherRequest->id} was {$this->type}",
        ];
    }

    private function getStatusEmoji($type)
    {
        return match ($type) {
            'created' => '🆕',
            'approved' => '✅',
            'rejected' => '❌',
            'completed' => '🏁',
            'received' => '📦',
            default => '📝',
        };
    }

    private function getTitle($type)
    {
        return match ($type) {
            'created' => 'คำขอเบิก/ยืมใหม่',
            'approved' => 'คำขอได้รับการอนุมัติ',
            'rejected' => 'คำขอถูกปฏิเสธ',
            'completed' => 'ดำเนินการเรียบร้อย',
            'received' => 'ยืนยันรับของแล้ว',
            default => 'อัปเดตสถานะคำขอ',
        };
    }
}
