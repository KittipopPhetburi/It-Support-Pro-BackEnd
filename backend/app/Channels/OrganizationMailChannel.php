<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Models\SystemSetting;
use App\Models\OrganizationNotification;
use Illuminate\Support\Facades\Log;

class OrganizationMailChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toMail')) {
            return;
        }

        // 1. Determine Context & Candidates
        $organization = null;
        $department = null;
        $branchName = null;
        $reqType = null;

        if (property_exists($notification, 'incident')) {
            $incident = $notification->incident;
            $organization = $incident->organization;
            $department = $incident->department;
            $branchName = $incident->branch ? $incident->branch->name : null;
            $reqType = 'incident';
        } elseif (property_exists($notification, 'assetRequest')) {
            $assetRequest = $notification->assetRequest;
            $organization = $assetRequest->organization;
            $department = $assetRequest->department;
            $branchName = $assetRequest->branch ? $assetRequest->branch->name : null;
            $reqType = strtolower($assetRequest->request_type ?? 'asset_request');
        } elseif (property_exists($notification, 'otherRequest')) {
            $otherRequest = $notification->otherRequest;
            $organization = $otherRequest->organization;
            $department = $otherRequest->department;
            $branchName = $otherRequest->branch ? $otherRequest->branch->name : null;
            $reqType = strtolower($otherRequest->request_type ?? 'requisition');
        }

        // 2. Build prioritized candidate list
        // Priority: 1. Organization, 2. Department, 3. Branch
        $candidates = array_filter(array_unique([
            $organization,
            $department,
            $branchName
        ]));

        if (empty($candidates) || !$reqType) {
            Log::warning("OrganizationMailChannel: Could not determine any organizational candidates for notification.");
            return;
        }

        // 3. Search for enabled settings with recipients among candidates
        $orgNotif = null;
        $foundCandidate = null;

        foreach ($candidates as $candidate) {
            $hexCandidate = bin2hex($candidate);
            Log::info("OrganizationMailChannel: Checking candidate '{$candidate}' (hex: {$hexCandidate}) for type '{$reqType}'");
            
            $setting = OrganizationNotification::where('organization_name', $candidate)
                ->where('request_type', $reqType)
                ->first();

            if (!$setting) {
                // Secondary check: search by LIKE to see if there's a nearby match
                $nearby = OrganizationNotification::where('organization_name', 'LIKE', '%' . $candidate . '%')
                    ->where('request_type', $reqType)
                    ->first();
                if ($nearby) {
                    $hexNearby = bin2hex($nearby->organization_name);
                    Log::warning("OrganizationMailChannel: Exact match failed for '{$candidate}', but found LIKE match: '{$nearby->organization_name}' (hex: {$hexNearby})");
                }
            }

            if ($setting && $setting->email_enabled && !empty($setting->email_recipients)) {
                $orgNotif = $setting;
                $foundCandidate = $candidate;
                break;
            }
        }

        if (!$orgNotif) {
            Log::info("OrganizationMailChannel: No enabled settings with recipients found for candidates: [" . implode(', ', $candidates) . "] and type '{$reqType}'");
            return;
        }

        // 4. Configure SMTP dynamically from System Settings
        $this->configureSmtp();

        // 5. Send Email
        try {
            $mailMessage = $notification->toMail($notifiable);
            
            $recipientString = $orgNotif->email_recipients ?? '';
            $recipients = array_filter(array_map('trim', explode(',', $recipientString)));
            
            if (empty($recipients)) {
                Log::warning("OrganizationMailChannel: No valid recipients found for '{$foundCandidate}' type '{$reqType}'");
                return;
            }

            Log::info("OrganizationMailChannel: Attempting to send email via candidate '{$foundCandidate}' to: [" . implode(', ', $recipients) . "]");
            
            // Purge the mailer to ensure it uses the new configuration
            Mail::purge();
            
            // Build custom HTML email instead of using Laravel's default template (removes logo)
            $html = $this->buildEmailHtml($mailMessage);
            Mail::html($html, function ($message) use ($recipients, $mailMessage) {
                $message->to($recipients);
                $message->subject($mailMessage->subject);
            });
            
            Log::info("OrganizationMailChannel: Email successfully sent via '{$foundCandidate}' for type '{$reqType}'");
        } catch (\Exception $e) {
            Log::error("OrganizationMailChannel Failed: " . $e->getMessage());
            Log::error("Exception line: " . $e->getLine());
            Log::error($e->getTraceAsString());
        } catch (\Throwable $t) {
            Log::error("OrganizationMailChannel Fatal Error: " . $t->getMessage());
            Log::error("Error line: " . $t->getLine());
        }
        Log::info("OrganizationMailChannel: Finished send process.");
    }

    /**
     * Configure SMTP settings from the system_settings table
     */
    private function configureSmtp()
    {
        $settings = SystemSetting::where('category', 'Email')
            ->get()
            ->keyBy('key');

        $mailDriver = $settings['mail_driver']->value ?? 'smtp';
        Config::set('mail.default', $mailDriver);

        if ($mailDriver === 'smtp') {
            // Note: smtp_username may be mail_username in some DBs, 
            // but controller uses smtp_username. We'll check both.
            $user = $settings['smtp_username']->value ?? ($settings['mail_username']->value ?? '');
            $pass = $settings['smtp_password']->value ?? ($settings['mail_password']->value ?? '');

            Config::set('mail.mailers.smtp', [
                'transport' => 'smtp',
                'host' => $settings['smtp_host']->value ?? 'smtp.gmail.com',
                'port' => $settings['smtp_port']->value ?? 587,
                'encryption' => $settings['smtp_encryption']->value ?? 'tls',
                'username' => $user,
                'password' => $pass,
                'timeout' => null,
                'local_domain' => env('MAIL_EHLO_DOMAIN'),
            ]);
        }

        Config::set('mail.from', [
            'address' => $settings['mail_from_address']->value ?? 'noreply@example.com',
            'name' => $settings['mail_from_name']->value ?? 'IT Support System',
        ]);
    }

    /**
     * Build clean HTML email from MailMessage (without Laravel logo/branding)
     */
    private function buildEmailHtml($mailMessage): string
    {
        $greeting = $mailMessage->greeting ?? 'แจ้งเตือน';
        $salutation = $mailMessage->salutation ?? 'ขอบคุณที่ใช้บริการ IT Support Pro';

        // Build content lines
        $linesHtml = '';
        foreach ($mailMessage->introLines as $line) {
            // Convert **bold** markdown to <strong>, then escape the rest
            $line = preg_replace('/\*\*(.+?)\*\*/', '==STRONG==$1==/STRONG==', $line);
            $line = e($line);
            $line = str_replace(['==STRONG==', '==/STRONG=='], ['<strong>', '</strong>'], $line);
            $linesHtml .= "<p style=\"margin: 8px 0; color: #374151; font-size: 15px; line-height: 1.6;\">{$line}</p>\n";
        }
        foreach ($mailMessage->outroLines as $line) {
            $line = preg_replace('/\*\*(.+?)\*\*/', '==STRONG==$1==/STRONG==', $line);
            $line = e($line);
            $line = str_replace(['==STRONG==', '==/STRONG=='], ['<strong>', '</strong>'], $line);
            $linesHtml .= "<p style=\"margin: 8px 0; color: #374151; font-size: 15px; line-height: 1.6;\">{$line}</p>\n";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.07);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); padding: 28px 40px;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 600;">
                                {$greeting}
                            </h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 32px 40px;">
                            {$linesHtml}
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 40px 28px; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; color: #6b7280; font-size: 14px;">
                                {$salutation}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}
