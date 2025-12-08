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

    public function testNotification($id, $channel)
    {
        $notification = OrganizationNotification::findOrFail($id);

        try {
            if ($channel === 'email') {
                return $this->testEmail($notification);
            } elseif ($channel === 'telegram') {
                return $this->testTelegram($notification);
            } elseif ($channel === 'line') {
                return $this->testLine($notification);
            }

            return response()->json(['success' => false, 'message' => 'Invalid channel'], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function testEmail($notification)
    {
        if (!$notification->email_enabled || !$notification->email_recipients) {
            return response()->json(['success' => false, 'message' => 'Email not configured'], 400);
        }

        // Get email settings from system_settings table
        $settings = SystemSetting::where('category', 'Email')
            ->get()
            ->keyBy('key');

        $mailDriver = $settings['mail_driver']->value ?? 'log';

        // Configure mail driver
        Config::set('mail.default', $mailDriver);

        if ($mailDriver === 'smtp') {
            // Configure mail settings dynamically
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
        
        Mail::raw(
            "This is a test notification from IT Support System.\n\n" .
            "Organization: {$notification->organization_name}\n" .
            "Request Type: {$notification->request_type}\n\n" .
            "If you received this email, your email notification is configured correctly.",
            function ($message) use ($recipients, $notification, $settings) {
                $message->to($recipients)
                        ->subject("Test Notification - {$notification->organization_name}")
                        ->from(
                            $settings['mail_from_address']->value ?? 'noreply@itsupport.com',
                            $settings['mail_from_name']->value ?? 'IT Support System'
                        );
            }
        );

        return response()->json(['success' => true, 'message' => 'Test email sent successfully']);
    }

    private function testTelegram($notification)
    {
        if (!$notification->telegram_enabled || !$notification->telegram_token || !$notification->telegram_chat_id) {
            return response()->json(['success' => false, 'message' => 'Telegram not configured'], 400);
        }

        $message = "🔔 *Test Notification*\n\n" .
                   "Organization: {$notification->organization_name}\n" .
                   "Request Type: {$notification->request_type}\n\n" .
                   "If you received this message, your Telegram notification is configured correctly.";

        $response = Http::post("https://api.telegram.org/bot{$notification->telegram_token}/sendMessage", [
            'chat_id' => $notification->telegram_chat_id,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);

        if ($response->successful()) {
            return response()->json(['success' => true, 'message' => 'Test Telegram message sent successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Telegram API error'], 500);
    }

    private function testLine($notification)
    {
        if (!$notification->line_enabled || !$notification->line_token) {
            return response()->json(['success' => false, 'message' => 'Line not configured'], 400);
        }

        $message = "\n🔔 Test Notification\n\n" .
                   "Organization: {$notification->organization_name}\n" .
                   "Request Type: {$notification->request_type}\n\n" .
                   "If you received this message, your Line notification is configured correctly.";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $notification->line_token,
        ])->asForm()->post('https://notify-api.line.me/api/notify', [
            'message' => $message
        ]);

        if ($response->successful()) {
            return response()->json(['success' => true, 'message' => 'Test Line message sent successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Line API error'], 500);
    }
}
