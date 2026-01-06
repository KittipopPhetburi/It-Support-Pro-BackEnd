<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Branch;
use App\Models\OrganizationNotification;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Iterate through all branches
        $branches = Branch::all();

        foreach ($branches as $branch) {
            // Find matching config in OrganizationNotification table (case-insensitive search if needed)
            // Assuming strict name match first
            $config = OrganizationNotification::where('organization_name', $branch->name)->first();

            if ($config && !empty($config->telegram_chat_id)) {
                $cleanedChatId = preg_replace('/\s+/u', '', $config->telegram_chat_id);
                
                // Update branch with Chat ID
                $branch->telegram_chat_id = $cleanedChatId;
                
                // Enable notifications in config by default if Chat ID exists
                $branch->notification_config = [
                    'incident' => true,
                    'asset_request' => true,
                    'other_request' => true
                ];
                
                $branch->save();
                
                echo "Synced config for branch: {$branch->name} (Chat ID: {$cleanedChatId})\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse data sync usually, or could set null
    }
};
