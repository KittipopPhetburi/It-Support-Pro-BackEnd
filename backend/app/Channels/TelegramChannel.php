<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class TelegramChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toTelegram')) {
            return;
        }

        $message = $notification->toTelegram($notifiable);
        $chatId = null;
        $shouldSend = false;

        // 1. Determine Context & Branch
        $branch = null;
        $configKey = null;

        if (property_exists($notification, 'incident')) {
            $branch = $notification->incident->branch;
            $configKey = 'incident';
            Log::info("TelegramChannel Debug: Context Incident ID: " . $notification->incident->id . ", Branch ID: " . ($notification->incident->branch_id ?? 'NULL'));
        } elseif (property_exists($notification, 'assetRequest')) {
            $branch = $notification->assetRequest->branch;
            $configKey = 'asset_request';
            Log::info("TelegramChannel Debug: Context AssetRequest ID: " . $notification->assetRequest->id);
        } elseif (property_exists($notification, 'otherRequest')) {
            $branch = $notification->otherRequest->branch;
            $configKey = 'other_request';
        }

        // 2. Check Branch Settings
        if ($branch && !empty($branch->telegram_chat_id)) {
            Log::info("TelegramChannel Debug: Branch '{$branch->name}' found. Chat ID: {$branch->telegram_chat_id}");
            
            // If branch has Chat ID, check if this notification type is enabled
            $config = $branch->notification_config ?? [];
            $isEnabled = $config[$configKey] ?? true;

            Log::info("TelegramChannel Debug: Config Key '{$configKey}' is " . ($isEnabled ? 'ENABLED' : 'DISABLED'));

            if ($isEnabled) {
                $chatId = $branch->telegram_chat_id;
                $shouldSend = true;
            } else {
                Log::info("Notification skipped for branch {$branch->name}: {$configKey} disabled in config.");
                return;
            }
        } else {
            Log::warning("TelegramChannel Debug: No Branch found OR No Chat ID for Branch. Model Branch ID: " . ($branch ? $branch->id : 'NULL'));
            if ($branch) {
                Log::warning("TelegramChannel Debug: Branch '{$branch->name}' has no telegram_chat_id");
            }
        } 
        
        // 3. Global Fallback (REMOVED as per user request)
        // User: "ไม่มีการส่งเข้ากลุ่มกลางแล้ว: ถ้าสาขาไหนไม่ตั้งค่า = เงียบกริบ"
        if (!$chatId) {
             // Log::info("Notification skipped: No branch Chat ID found.");
             return;
        }

        if (!$shouldSend) {
            return;
        }

        $botToken = SystemSetting::where('key', 'telegram_bot_token')->value('value') ?? env('TELEGRAM_BOT_TOKEN');

        if (!$botToken || !$chatId) {
            Log::warning('Telegram Notification failed: Missing Token or Chat ID');
            return;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                Log::error('Telegram API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Telegram Notification failed: ' . $e->getMessage());
        }
    }
}
