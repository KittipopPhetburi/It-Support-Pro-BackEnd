<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SystemSetting;

class SystemSettingsSeeder extends Seeder
{
    public function run()
    {
        // Default System Settings
        $settings = [
            // Email Settings
            ['category' => 'Email', 'key' => 'mail_host', 'value' => 'smtp.gmail.com', 'description' => 'SMTP Host Address', 'type' => 'text'],
            ['category' => 'Email', 'key' => 'mail_port', 'value' => '587', 'description' => 'SMTP Port (587 or 465)', 'type' => 'number'],
            ['category' => 'Email', 'key' => 'mail_username', 'value' => 'example@gmail.com', 'description' => 'SMTP Username', 'type' => 'text'],
            ['category' => 'Email', 'key' => 'mail_password', 'value' => 'password', 'description' => 'SMTP Password / App Password', 'type' => 'password'],
            ['category' => 'Email', 'key' => 'mail_encryption', 'value' => 'tls', 'description' => 'Encryption (tls/ssl)', 'type' => 'text'],
            ['category' => 'Email', 'key' => 'mail_from_address', 'value' => 'noreply@company.com', 'description' => 'Default Sender Email Address', 'type' => 'text'],
            ['category' => 'Email', 'key' => 'mail_from_name', 'value' => 'IT Support Pro', 'description' => 'Default Sender Name', 'type' => 'text'],

            // System Settings
            ['category' => 'System', 'key' => 'auto_close_days', 'value' => '7', 'description' => 'Auto-close resolved incidents after (days)', 'type' => 'number'],
            ['category' => 'System', 'key' => 'max_upload_size', 'value' => '10', 'description' => 'Max upload file size (MB)', 'type' => 'number'],
            
            // SLA Settings (Hours)
            ['category' => 'SLA', 'key' => 'sla_critical_response', 'value' => '1', 'description' => 'Critical Priority Response Time', 'type' => 'number'],
            ['category' => 'SLA', 'key' => 'sla_critical_resolution', 'value' => '4', 'description' => 'Critical Priority Resolution Time', 'type' => 'number'],
            ['category' => 'SLA', 'key' => 'sla_high_response', 'value' => '2', 'description' => 'High Priority Response Time', 'type' => 'number'],
            ['category' => 'SLA', 'key' => 'sla_high_resolution', 'value' => '8', 'description' => 'High Priority Resolution Time', 'type' => 'number'],
            ['category' => 'SLA', 'key' => 'sla_medium_response', 'value' => '4', 'description' => 'Medium Priority Response Time', 'type' => 'number'],
            ['category' => 'SLA', 'key' => 'sla_medium_resolution', 'value' => '24', 'description' => 'Medium Priority Resolution Time', 'type' => 'number'],
            ['category' => 'SLA', 'key' => 'sla_low_response', 'value' => '8', 'description' => 'Low Priority Response Time', 'type' => 'number'],
            ['category' => 'SLA', 'key' => 'sla_low_resolution', 'value' => '48', 'description' => 'Low Priority Resolution Time', 'type' => 'number'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']], // Check by key
                $setting // Update or create with these values
            );
        }

        $this->command?->info('System settings seeded successfully.');
    }
}
