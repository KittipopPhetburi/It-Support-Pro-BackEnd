<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Channels\TelegramChannel;
use App\Models\Incident;

class IncidentNotification extends Notification
{
    // use Queueable; // Disable queue for immediate sending

    public $incident;
    protected $type; // 'created', 'updated', 'resolved', 'closed'
    protected $actorName;
    protected $newStatus;

    public function __construct(Incident $incident, string $type = 'created', ?string $actorName = null, ?string $newStatus = null)
    {
        $this->incident = $incident;
        $this->type = $type;
        $this->actorName = $actorName;
        $this->newStatus = $newStatus;
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
        $incident = $this->incident;
        $title = $this->getTitle($this->type);
        $statusEmoji = $this->getStatusEmoji($this->type);
        $priorityEmoji = $this->getPriorityEmoji($incident->priority);
        
        $mail = (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject("[{$title}] #{$incident->ticket_id} - {$incident->title}")
            ->greeting("{$statusEmoji} {$title}")
            ->line("ขณะนี้รายการแจ้งซ่อมของท่านมีการอัปเดตสถานะ")
            ->line("**เลขที่ตั๋ว:** #{$incident->ticket_id}")
            ->line("**หัวข้อ:** {$incident->title}")
            ->line("**สถานะ:** " . ($this->newStatus ?? $incident->status))
            ->line("**ความสำคัญ:** {$priorityEmoji} {$incident->priority}")
            ->line("**ผู้แจ้ง:** " . ($incident->requester ? $incident->requester->name : ($incident->requester_name ?? '-')));

        // แสดงผู้ซ่อม (Technician)
        if ($this->actorName) {
            $mail->line("**ดำเนินการโดย:** {$this->actorName}");
        } elseif ($incident->assignee) {
            $mail->line("**ผู้ซ่อม:** {$incident->assignee->name}");
        }

        // แสดงสถานที่
        if ($incident->location) {
            $mail->line("**สถานที่:** {$incident->location}");
        }

        // แสดงรายละเอียดเฉพาะ incident ใหม่
        if ($this->type === 'created' && $incident->description) {
            $mail->line("**รายละเอียด:** {$incident->description}");
        }

        // แสดงวิธีแก้ไขถ้า resolved
        if ($this->type === 'resolved' && $incident->resolution_notes) {
            $mail->line("**วิธีแก้ไข:** {$incident->resolution_notes}");
        }

        $mail->line("📅 " . now()->setTimezone('Asia/Bangkok')->format('d/m/Y H:i'));
        $mail->salutation("ขอบคุณที่ใช้บริการ IT Support Pro");

        return $mail;
    }

    public function toTelegram($notifiable)
    {
        $incident = $this->incident;
        $statusEmoji = $this->getStatusEmoji($this->type);
        $title = $this->getTitle($this->type);
        $priorityEmoji = $this->getPriorityEmoji($incident->priority);

        $message = "<b>{$statusEmoji} {$title}</b>\n\n";
        $message .= "<b>Ticket:</b> #{$incident->ticket_id}\n";
        $message .= "<b>เรื่อง:</b> {$incident->title}\n";
        $message .= "<b>ผู้แจ้ง:</b> " . ($incident->requester ? $incident->requester->name : $incident->requester_name) . "\n"; // Try relation first
        if ($incident->location) {
            $message .= "<b>สถานที่:</b> {$incident->location}\n";
        }
        $message .= "<b>ความสำคัญ:</b> {$priorityEmoji} {$incident->priority}\n";
        
        // Show new status if available
        if ($this->newStatus) {
            $message .= "<b>สถานะล่าสุด:</b> {$this->newStatus}\n";
        }

        if ($this->type === 'created') {
             $message .= "<b>รายละเอียด:</b> {$incident->description}\n";
        }
        
        // Show who performed the action (actor) or the assigned technician
        if ($this->actorName) {
            $message .= "<b>ดำเนินการโดย:</b> {$this->actorName}\n";
        } elseif ($incident->assignee) { // Use relation instead of flat prop
            $message .= "<b>ผู้ดูแล:</b> {$incident->assignee->name}\n";
        }

        if ($this->type === 'resolved' && $incident->resolution_notes) {
            $message .= "<b>วิธีแก้ไข:</b> {$incident->resolution_notes}\n";
        }

        $message .= "\n📅 " . now()->setTimezone('Asia/Bangkok')->format('d/m/Y H:i');

        return $message;
    }

    public function toArray($notifiable)
    {
        $message = "Incident #{$this->incident->ticket_id} was {$this->type}";
        if ($this->actorName) {
            $message .= " by {$this->actorName}";
        }

        return [
            'incident_id' => $this->incident->id,
            'ticket_id' => $this->incident->ticket_id,
            'title' => $this->incident->title,
            'status' => $this->type,
            'message' => $message,
            'actor_name' => $this->actorName,
            'new_status' => $this->newStatus,
        ];
    }

    private function getStatusEmoji($type)
    {
        return match ($type) {
            'created' => '🚨',
            'updated' => '📝',
            'resolved' => '✅',
            'closed' => '🔒',
            default => 'ℹ️',
        };
    }

    private function getTitle($type)
    {
        return match ($type) {
            'created' => 'แจ้งซ่อมใหม่ (New Incident)',
            'updated' => 'อัปเดตงานซ่อม',
            'resolved' => 'งานซ่อมแก้ไขแล้ว',
            'closed' => 'ปิดงานซ่อม',
            default => 'อัปเดตสถานะ',
        };
    }

    private function getPriorityEmoji($priority)
    {
        return match ($priority) {
            'Critical' => '🔥',
            'High' => '🔴',
            'Medium' => '🟡',
            'Low' => '🟢',
            default => '⚪',
        };
    }
}
