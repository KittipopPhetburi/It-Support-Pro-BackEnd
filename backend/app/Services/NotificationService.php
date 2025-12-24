<?php

namespace App\Services;

use App\Models\OrganizationNotification;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification for a specific event type
     *
     * @param string $organizationName The organization/branch name
     * @param string $requestType The type of request (incident, requisition, borrow, replace)
     * @param array $data Event data for the notification message
     * @return void
     */
    public function sendNotification(string $organizationName, string $requestType, array $data): void
    {
        // Find notification settings for this organization and request type
        $notification = OrganizationNotification::where('organization_name', $organizationName)
            ->where('request_type', $requestType)
            ->first();

        if (!$notification) {
            Log::info("No notification settings found for {$organizationName} - {$requestType}");
            return;
        }

        $message = $this->formatMessage($requestType, $data);

        // Send via enabled channels
        if ($notification->email_enabled && $notification->email_recipients) {
            $this->sendEmail($notification, $message, $data);
        }

        if ($notification->telegram_enabled && $notification->telegram_token && $notification->telegram_chat_id) {
            $this->sendTelegram($notification, $message);
        }

        if ($notification->line_enabled && $notification->line_token) {
            $this->sendLine($notification, $message);
        }
    }

    /**
     * Format message based on request type
     */
    private function formatMessage(string $requestType, array $data): string
    {
        $now = now()->format('d/m/Y H:i');
        
        switch ($requestType) {
            case 'incident':
                return "🔧 *แจ้งซ่อมใหม่*\n\n" .
                       "หัวข้อ: {$data['title']}\n" .
                       "ความสำคัญ: {$data['priority']}\n" .
                       "หน่วยงาน: {$data['organization']}\n" .
                       "ผู้แจ้ง: {$data['requester_name']}\n" .
                       (!empty($data['asset_name']) ? "อุปกรณ์: {$data['asset_name']}\n" : "") .
                       "เวลา: {$now}";
            
            case 'requisition':
                return "📦 *คำขอเบิกอุปกรณ์*\n\n" .
                       "รายการ: {$data['title']}\n" .
                       "หน่วยงาน: {$data['organization']}\n" .
                       "ผู้ขอ: {$data['requester_name']}\n" .
                       "เวลา: {$now}";
            
            case 'borrow':
                return "🔄 *คำขอยืมอุปกรณ์*\n\n" .
                       "รายการ: {$data['title']}\n" .
                       "หน่วยงาน: {$data['organization']}\n" .
                       "ผู้ขอ: {$data['requester_name']}\n" .
                       "เวลา: {$now}";
            
            case 'replace':
                return "🔃 *คำขอทดแทนอุปกรณ์*\n\n" .
                       "รายการ: {$data['title']}\n" .
                       "หน่วยงาน: {$data['organization']}\n" .
                       "ผู้ขอ: {$data['requester_name']}\n" .
                       "เวลา: {$now}";
            
            default:
                return "🔔 *แจ้งเตือน*\n\n" .
                       "ประเภท: {$requestType}\n" .
                       "หน่วยงาน: {$data['organization']}\n" .
                       "เวลา: {$now}";
        }
    }

    /**
     * Send email notification
     */
    private function sendEmail(OrganizationNotification $notification, string $message, array $data): void
    {
        try {
            // Get email settings from system_settings table
            $settings = SystemSetting::where('category', 'Email')
                ->get()
                ->keyBy('key');

            $mailDriver = $settings['mail_driver']->value ?? 'log';

            // Configure mail driver
            Config::set('mail.default', $mailDriver);

            if ($mailDriver === 'smtp') {
                Config::set('mail.mailers.smtp', [
                    'transport' => 'smtp',
                    'host' => $settings['smtp_host']->value ?? 'smtp.gmail.com',
                    'port' => $settings['smtp_port']->value ?? 587,
                    'encryption' => $settings['smtp_encryption']->value ?? 'tls',
                    'username' => $settings['smtp_username']->value ?? '',
                    'password' => $settings['smtp_password']->value ?? '',
                ]);
            }

            Config::set('mail.from', [
                'address' => $settings['mail_from_address']->value ?? 'noreply@itsupport.com',
                'name' => $settings['mail_from_name']->value ?? 'IT Support System',
            ]);

            $recipients = array_map('trim', explode(',', $notification->email_recipients));
            $subject = $this->getEmailSubject($notification->request_type, $data);

            // Convert markdown message to plain text for email
            $plainMessage = str_replace(['*', '_'], '', $message);
            
            Mail::raw(
                $plainMessage,
                function ($mail) use ($recipients, $subject, $settings) {
                    $mail->to($recipients)
                         ->subject($subject)
                         ->from(
                             $settings['mail_from_address']->value ?? 'noreply@itsupport.com',
                             $settings['mail_from_name']->value ?? 'IT Support System'
                         );
                }
            );

            Log::info("Email notification sent to: " . implode(', ', $recipients));
        } catch (\Exception $e) {
            Log::error("Failed to send email notification: " . $e->getMessage());
        }
    }

    /**
     * Send Telegram notification
     */
    private function sendTelegram(OrganizationNotification $notification, string $message): void
    {
        try {
            $response = Http::post("https://api.telegram.org/bot{$notification->telegram_token}/sendMessage", [
                'chat_id' => $notification->telegram_chat_id,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);

            if ($response->successful()) {
                Log::info("Telegram notification sent to chat: {$notification->telegram_chat_id}");
            } else {
                Log::error("Telegram API error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Failed to send Telegram notification: " . $e->getMessage());
        }
    }

    /**
     * Send Line notification
     */
    private function sendLine(OrganizationNotification $notification, string $message): void
    {
        try {
            // Convert markdown to plain text for Line
            $plainMessage = str_replace(['*', '_'], '', $message);
            
            $ch = curl_init('https://notify-api.line.me/api/notify');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $notification->line_token,
                    'Content-Type: application/x-www-form-urlencoded',
                ],
                CURLOPT_POSTFIELDS => http_build_query(['message' => $plainMessage]),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                Log::info("Line notification sent successfully");
            } else {
                Log::error("Line API error: HTTP {$httpCode} - {$result}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send Line notification: " . $e->getMessage());
        }
    }

    /**
     * Get email subject based on request type
     */
    private function getEmailSubject(string $requestType, array $data): string
    {
        $org = $data['organization'] ?? 'Unknown';
        
        switch ($requestType) {
            case 'incident':
                return "[แจ้งซ่อม] {$data['title']} - {$org}";
            case 'requisition':
                return "[เบิกอุปกรณ์] {$data['title']} - {$org}";
            case 'borrow':
                return "[ยืมอุปกรณ์] {$data['title']} - {$org}";
            case 'replace':
                return "[ทดแทนอุปกรณ์] {$data['title']} - {$org}";
            default:
                return "[แจ้งเตือน IT Support] {$org}";
        }
    }
}
