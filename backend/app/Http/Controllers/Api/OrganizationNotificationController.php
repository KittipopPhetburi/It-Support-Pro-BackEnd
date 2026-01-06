<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganizationNotification;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class OrganizationNotificationController extends Controller
{
    public function initialize(Request $request)
    {
        $request->validate([
            'organization_name' => 'required|string',
        ]);

        $orgName = $request->organization_name;
        $types = ['incident', 'requisition', 'borrow', 'replace'];
        $createdNotifications = [];

        foreach ($types as $type) {
            $notification = OrganizationNotification::firstOrCreate(
                [
                    'organization_name' => $orgName,
                    'request_type' => $type
                ],
                [
                    'email_enabled' => false,
                    'telegram_enabled' => false,
                    'line_enabled' => false
                ]
            );
            $createdNotifications[] = $notification;
        }

        $formattedData = collect($createdNotifications)->map(function ($notif) {
            return [
                'id' => (string) $notif->id,
                'organizationName' => $notif->organization_name,
                'requestType' => $notif->request_type,
                'emailEnabled' => (boolean) $notif->email_enabled,
                'emailRecipients' => $notif->email_recipients ?? '',
                'telegramEnabled' => (boolean) $notif->telegram_enabled,
                'telegramToken' => $notif->telegram_token ?? '',
                'telegramChatId' => $notif->telegram_chat_id ?? '',
                'lineEnabled' => (boolean) $notif->line_enabled,
                'lineToken' => $notif->line_token ?? '',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedData
        ]);
    }

    public function index()
    {
        $notifications = OrganizationNotification::all()->map(function ($notif) {
            return [
                'id' => (string) $notif->id,
                'organizationName' => $notif->organization_name,
                'requestType' => $notif->request_type,
                'emailEnabled' => $notif->email_enabled,
                'emailRecipients' => $notif->email_recipients ?? '',
                'telegramEnabled' => $notif->telegram_enabled,
                'telegramToken' => $notif->telegram_token ?? '',
                'telegramChatId' => $notif->telegram_chat_id ?? '',
                'lineEnabled' => $notif->line_enabled,
                'lineToken' => $notif->line_token ?? '',
            ];
        });

        return response()->json($notifications);
    }

    public function update(Request $request, $id)
    {
        $notification = OrganizationNotification::findOrFail($id);
        
        $data = [];
        if ($request->has('emailEnabled')) $data['email_enabled'] = $request->emailEnabled;
        if ($request->has('emailRecipients')) $data['email_recipients'] = $request->emailRecipients;
        if ($request->has('telegramEnabled')) $data['telegram_enabled'] = $request->telegramEnabled;
        if ($request->has('telegramToken')) $data['telegram_token'] = $request->telegramToken;
        if ($request->has('telegramChatId')) $data['telegram_chat_id'] = $request->telegramChatId;
        if ($request->has('lineEnabled')) $data['line_enabled'] = $request->lineEnabled;
        if ($request->has('lineToken')) $data['line_token'] = $request->lineToken;

        $notification->update($data);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (string) $notification->id,
                'organizationName' => $notification->organization_name,
                'requestType' => $notification->request_type,
                'emailEnabled' => $notification->email_enabled,
                'emailRecipients' => $notification->email_recipients ?? '',
                'telegramEnabled' => $notification->telegram_enabled,
                'telegramToken' => $notification->telegram_token ?? '',
                'telegramChatId' => $notification->telegram_chat_id ?? '',
                'lineEnabled' => $notification->line_enabled,
                'lineToken' => $notification->line_token ?? '',
            ]
        ]);
    }

    public function testNotification(Request $request, $id, $channel)
    {
        $notification = OrganizationNotification::findOrFail($id);

        try {
            if ($channel === 'email') {
                return $this->testEmail($notification, $request->all());
            } elseif ($channel === 'telegram') {
                return $this->testTelegram($notification, $request->all());
            } elseif ($channel === 'line') {
                return $this->testLine($notification, $request->all());
            }

            return response()->json(['success' => false, 'message' => 'Invalid channel'], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function testEmail($notification)
    {
        if (!$notification->email_recipients) {
            return response()->json(['success' => false, 'message' => 'กรุณากรอกอีเมลผู้รับก่อน'], 400);
        }

        try {
            // Get email settings from system_settings table
            $settings = SystemSetting::where('category', 'Email')
                ->get()
                ->keyBy('key');

            // Check if we have any settings
            if ($settings->isEmpty()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'ยังไม่ได้ตั้งค่า Email กรุณาไปที่แท็บ Email เพื่อกำหนด SMTP settings'
                ], 400);
            }

            $mailDriver = $settings['mail_driver']->value ?? 'smtp';
            $smtpUsername = $settings['smtp_username']->value ?? '';
            $smtpPassword = $settings['smtp_password']->value ?? '';

            // Check SMTP credentials
            if ($mailDriver === 'smtp' && (empty($smtpUsername) || empty($smtpPassword))) {
                return response()->json([
                    'success' => false, 
                    'message' => 'กรุณากำหนด SMTP Username และ Password ในแท็บ Email ก่อนทดสอบ'
                ], 400);
            }

            // Configure mail driver
            Config::set('mail.default', $mailDriver);

            if ($mailDriver === 'smtp') {
                // Configure mail settings dynamically
                Config::set('mail.mailers.smtp', [
                    'transport' => 'smtp',
                    'host' => $settings['smtp_host']->value ?? 'smtp.gmail.com',
                    'port' => (int)($settings['smtp_port']->value ?? 587),
                    'encryption' => $settings['smtp_encryption']->value ?? 'tls',
                    'username' => $smtpUsername,
                    'password' => $smtpPassword,
                    'verify_peer' => false, // Disable SSL verification for development
                ]);
            }

            $fromAddress = $settings['mail_from_address']->value ?? $smtpUsername;
            $fromName = $settings['mail_from_name']->value ?? 'IT Support System';

            Config::set('mail.from', [
                'address' => $fromAddress,
                'name' => $fromName,
            ]);

            $recipients = array_map('trim', explode(',', $notification->email_recipients));
            
            \Log::info('Sending test email', [
                'to' => $recipients,
                'driver' => $mailDriver,
                'host' => $settings['smtp_host']->value ?? 'smtp.gmail.com',
            ]);

            Mail::raw(
                "นี่คือข้อความทดสอบจากระบบ IT Support System\n\n" .
                "สาขา: {$notification->organization_name}\n" .
                "ประเภท: {$notification->request_type}\n\n" .
                "หากคุณได้รับอีเมลนี้ แสดงว่าการตั้งค่าอีเมลถูกต้อง",
                function ($message) use ($recipients, $notification, $fromAddress, $fromName) {
                    $message->to($recipients)
                            ->subject("ทดสอบการแจ้งเตือน - {$notification->organization_name}")
                            ->from($fromAddress, $fromName);
                }
            );

            return response()->json(['success' => true, 'message' => 'ส่งอีเมลทดสอบสำเร็จ กรุณาตรวจสอบกล่องจดหมาย']);
        } catch (\Exception $e) {
            \Log::error('Email test failed: ' . $e->getMessage());
            
            $errorMsg = $e->getMessage();
            
            // Translate common errors
            if (strpos($errorMsg, 'authentication') !== false || strpos($errorMsg, 'AUTHENTICATION') !== false) {
                $errorMsg = 'การยืนยันตัวตนล้มเหลว กรุณาตรวจสอบ SMTP Username/Password (สำหรับ Gmail ใช้ App Password)';
            } elseif (strpos($errorMsg, 'Connection') !== false || strpos($errorMsg, 'connection') !== false) {
                $errorMsg = 'ไม่สามารถเชื่อมต่อ SMTP Server ได้ กรุณาตรวจสอบ Host และ Port';
            }
            
            return response()->json(['success' => false, 'message' => $errorMsg], 500);
        }
    }

    private function testTelegram($notification, $data = [])
    {
        // Use preg_replace to remove ALL whitespace/invisible characters
        $rawToken = $data['telegramToken'] ?? $notification->telegram_token;
        $rawChatId = $data['telegramChatId'] ?? $notification->telegram_chat_id;

        $token = preg_replace('/\s+/u', '', $rawToken ?? '');
        $chatId = preg_replace('/\s+/u', '', $rawChatId ?? '');

        if (!$token || !$chatId) {
            return response()->json(['success' => false, 'message' => 'Telegram credentials not configured. Please enter Token and Chat ID first.'], 400);
        }

        $message = "🔔 *Test Notification*\n\n" .
                   "Organization: {$notification->organization_name}\n" .
                   "Request Type: {$notification->request_type}\n\n" .
                   "If you received this message, your Telegram notification is configured correctly.";

        try {
            // Use cURL directly to avoid Laravel HTTP client issues
            $ch = curl_init("https://api.telegram.org/bot{$token}/sendMessage");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown'
                ]),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_TIMEOUT => 30,
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);


            if ($httpCode === 200) {
                return response()->json(['success' => true, 'message' => 'Test Telegram message sent successfully']);
            }

            return response()->json(['success' => false, 'message' => 'Telegram API error: ' . $result], 500);
        } catch (\Exception $e) {
             return response()->json(['success' => false, 'message' => 'Connection error: ' . $e->getMessage()], 500);
        }
    }

    private function testLine($notification)
    {
        if (!$notification->line_token) {
            return response()->json(['success' => false, 'message' => 'Line token not configured'], 400);
        }

        $message = "\n🔔 Test Notification\n\n" .
                   "Organization: {$notification->organization_name}\n" .
                   "Request Type: {$notification->request_type}\n\n" .
                   "If you received this message, your Line notification is configured correctly.";

        try {
            // Use cURL directly with IP resolution
            $ch = curl_init('https://notify-api.line.me/api/notify');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $notification->line_token,
                    'Content-Type: application/x-www-form-urlencoded',
                ],
                CURLOPT_POSTFIELDS => http_build_query(['message' => $message]),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                CURLOPT_RESOLVE => ['notify-api.line.me:443:203.104.153.18'],
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);

            \Log::info('LINE cURL Response', [
                'http_code' => $httpCode,
                'curl_error' => $curlError,
                'curl_errno' => $curlErrno,
                'result' => $result
            ]);

            if ($curlErrno) {
                return response()->json([
                    'success' => false,
                    'message' => 'DNS Error: Your network cannot resolve notify-api.line.me. Try using different DNS (8.8.8.8)'
                ], 500);
            }

            if ($httpCode === 200) {
                return response()->json(['success' => true, 'message' => 'Test Line message sent successfully']);
            }
            
            $decodedResult = json_decode($result, true);
            return response()->json([
                'success' => false, 
                'message' => 'Line API error: ' . ($decodedResult['message'] ?? 'Unknown error')
            ], 500);
            
        } catch (\Exception $e) {
            \Log::error('LINE Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Line error: ' . $e->getMessage()
            ], 500);
        }
    }
}
