<?php

namespace App\Http\Controllers\Api;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class SystemSettingController extends BaseCrudController
{
    protected string $modelClass = SystemSetting::class;

    protected array $validationRules = [
        'category' => 'nullable|string|max:255',
        'key' => 'nullable|string|max:255',
        'value' => 'required|string',
        'description' => 'nullable|string',
    ];

    /**
     * Test email configuration using system settings
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'test_recipient' => 'required|email'
        ]);

        try {
            // Get email settings from database
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

            // Send test email
            Mail::raw('This is a test email from IT Support System. If you receive this, your email configuration is working correctly.', function ($message) use ($request, $settings) {
                $message->to($request->test_recipient)
                    ->subject('Test Email - IT Support System')
                    ->from(
                        $settings['mail_from_address']->value ?? 'noreply@itsupport.com',
                        $settings['mail_from_name']->value ?? 'IT Support System'
                    );
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Test email failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }
}
