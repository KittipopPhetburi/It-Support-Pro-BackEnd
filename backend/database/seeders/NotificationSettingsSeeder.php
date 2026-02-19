<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrganizationNotification;
use App\Models\Branch;

class NotificationSettingsSeeder extends Seeder
{
    public function run()
    {
        // Get the default organization name (usually from the first branch or hardcoded 'IT Support Pro')
        // In this system, 'organization' is often stored as a string name on users/assets, 
        // but linked to Branch model. Let's use 'IT Support Pro' as the default Organization Name used in the App.
        $targetOrgName = 'IT Support Pro'; 
        
        // Ensure at least one branch exists to be safe
        $branch = Branch::first();
        if ($branch) {
            $targetOrgName = $branch->name; // Use the actual Main Branch name if available
        }

        $requestTypes = ['incident', 'requisition', 'borrow', 'replace'];

        foreach ($requestTypes as $type) {
            OrganizationNotification::firstOrCreate(
                [
                    'organization_name' => $targetOrgName,
                    'request_type' => $type
                ],
                [
                    'email_enabled' => false,
                    'email_recipients' => '', // e.g. 'admin@company.com'
                    'telegram_enabled' => false,
                    'telegram_token' => '',
                    'telegram_chat_id' => '',
                    'line_enabled' => false,
                    'line_token' => '',
                ]
            );
        }

        $this->command?->info("Notification settings seeded for organization: $targetOrgName");
    }
}
