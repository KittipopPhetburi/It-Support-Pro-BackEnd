<?php

namespace Database\Seeders;

use App\Models\OrganizationNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationNotificationsSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = DB::table('branches')
            ->select('organization')
            ->distinct()
            ->whereNotNull('organization')
            ->pluck('organization')
            ->toArray();

        if (empty($organizations)) {
            $organizations = ['Main Organization'];
        }

        $requestTypes = ['requisition', 'borrow', 'replace', 'incident'];

        foreach ($organizations as $organization) {
            foreach ($requestTypes as $requestType) {
                OrganizationNotification::updateOrCreate(
                    [
                        'organization_name' => $organization,
                        'request_type' => $requestType
                    ],
                    [
                        'email_enabled' => false,
                        'email_recipients' => '',
                        'telegram_enabled' => false,
                        'telegram_token' => '',
                        'telegram_chat_id' => '',
                        'line_enabled' => false,
                        'line_token' => '',
                    ]
                );
            }
        }
    }
}
