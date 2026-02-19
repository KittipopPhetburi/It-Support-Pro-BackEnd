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
        file_put_contents(base_path('telegram_debug.log'), date('Y-m-d H:i:s') . " - TelegramChannel::send Triggered\n", FILE_APPEND);

        if (!method_exists($notification, 'toTelegram')) {
            file_put_contents(base_path('telegram_debug.log'), date('Y-m-d H:i:s') . " - Method toTelegram missing\n", FILE_APPEND);
            return;
        }

        $message = $notification->toTelegram($notifiable);
        $chatId = null;
        $shouldSend = false;

        // 1. Determine Context & Candidates
        $organization = null;
        $department = null;
        $branch = null;
        $branchName = null;
        $reqType = null;
        $configKey = null;

        if (property_exists($notification, 'incident')) {
            $incident = $notification->incident;
            $branch = $incident->branch;
            $organization = $incident->organization;
            $department = $incident->department;
            $branchName = $branch ? $branch->name : null;
            $reqType = 'incident';
            $configKey = 'incident';
        } elseif (property_exists($notification, 'assetRequest')) {
            $assetRequest = $notification->assetRequest;
            $branch = $assetRequest->branch;
            $organization = $assetRequest->organization;
            $department = $assetRequest->department;
            $branchName = $branch ? $branch->name : null;
            $reqType = strtolower($assetRequest->request_type ?? 'asset_request');
            $configKey = $reqType;
        } elseif (property_exists($notification, 'otherRequest')) {
            $otherRequest = $notification->otherRequest;
            $branch = $otherRequest->branch;
            $organization = $otherRequest->organization;
            $department = $otherRequest->department;
            $branchName = $branch ? $branch->name : null;
            $reqType = strtolower($otherRequest->request_type ?? 'requisition');
            $configKey = 'other_request'; 
        }

        // 2. Build prioritized candidate list
        // Priority: 1. Organization, 2. Department, 3. Branch
        $candidates = array_filter(array_unique([
            $organization,
            $department,
            $branchName
        ]));

        file_put_contents(base_path('telegram_debug.log'), date('Y-m-d H:i:s') . " - Candidates: [" . implode(', ', $candidates) . "], Type: '$reqType'\n", FILE_APPEND);

        // 3. Search for enabled settings among candidates
        $orgNotif = null;
        $foundCandidate = null;

        foreach ($candidates as $candidate) {
            Log::info("TelegramChannel: Checking candidate '{$candidate}' for type '{$reqType}'");
            $setting = \App\Models\OrganizationNotification::where('organization_name', $candidate)
                ->where('request_type', $reqType)
                ->first();

            if ($setting && $setting->telegram_enabled && !empty($setting->telegram_chat_id)) {
                $orgNotif = $setting;
                $foundCandidate = $candidate;
                break;
            }
        }

        if ($orgNotif) {
            $chatId = $orgNotif->telegram_chat_id;
            $shouldSend = true;
            
            if (!empty($orgNotif->telegram_token)) {
                $customBotToken = $orgNotif->telegram_token;
            }
            Log::info("TelegramChannel: Using settings from candidate '{$foundCandidate}'. ChatID: {$chatId}");
            file_put_contents(base_path('telegram_debug.log'), date('Y-m-d H:i:s') . " - Found Candidate: $foundCandidate. ChatID: $chatId\n", FILE_APPEND);
            
            goto send_notification;
        }

        // 4. Fallback: Check Branch Settings (Legacy)
        if ($branch && !empty($branch->telegram_chat_id)) {
            Log::info("TelegramChannel Debug: Branch '{$branch->name}' found. Chat ID: {$branch->telegram_chat_id}");
            
            // If branch has Chat ID, check if this notification type is enabled
            $config = $branch->notification_config ?? [];
            $isEnabled = $config[$configKey] ?? true;

            if ($isEnabled) {
                $chatId = $branch->telegram_chat_id;
                $shouldSend = true;
            } else {
                Log::info("Notification skipped for branch {$branch->name}: {$configKey} disabled in branch config.");
                file_put_contents(base_path('telegram_debug.log'), date('Y-m-d H:i:s') . " - Skipped by Branch Config\n", FILE_APPEND);
                return;
            }
        } else {
            Log::warning("TelegramChannel Debug: No OrganizationNotification found AND No Branch Chat ID found.");
            file_put_contents(base_path('telegram_debug.log'), date('Y-m-d H:i:s') . " - No Config Found anywhere\n", FILE_APPEND);
        } 

        send_notification: 
        
        // 4. Global Fallback (REMOVED as per user request)
        if (!$chatId) {
             Log::warning("TelegramChannel: No config found for notification. Skipped.");
             file_put_contents(base_path('telegram_debug.log'), date('Y-m-d H:i:s') . " - Aborting: No ChatID\n", FILE_APPEND);
             return;
        }

        if (!$shouldSend) {
            return;
        }

        // Use custom token if available, otherwise default to SystemSetting or Env
        $botToken = $customBotToken 
            ?: (SystemSetting::where('key', 'telegram_bot_token')->value('value') ?? env('TELEGRAM_BOT_TOKEN'));

        if (!$botToken || !$chatId) {
            Log::warning('Telegram Notification failed: Missing Token or Chat ID');
            file_put_contents(base_path('telegram_debug.log'), date('Y-m-d H:i:s') . " - Aborting: Missing Token or Chat ID. Token len: " . strlen($botToken ?? '') . "\n", FILE_APPEND);
            return;
        }

        try {
            file_put_contents(base_path('telegram_debug.log'), date('Y-m-d H:i:s') . " - POSTing to Telegram API...\n", FILE_APPEND);
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            file_put_contents(base_path('telegram_debug.log'), date('Y-m-d H:i:s') . " - Response: " . $response->status() . " " . $response->body() . "\n", FILE_APPEND);

            if (!$response->successful()) {
                Log::error('Telegram API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Telegram Notification failed: ' . $e->getMessage());
            file_put_contents(base_path('telegram_debug.log'), date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}
