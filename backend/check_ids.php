<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Branch;
use App\Models\OrganizationNotification;

echo "--- Branches ---\n";
$hq = Branch::where('name', 'สำนักงานใหญ่')->first();
$hy = Branch::where('name', 'สาขาหาดใหญ่')->first();

echo "HQ (DB): " . ($hq ? $hq->telegram_chat_id : 'Not Found') . "\n";
echo "HY (DB): " . ($hy ? $hy->telegram_chat_id : 'Not Found') . "\n";

echo "\n--- Org Notifications (Source) ---\n";
$hqN = OrganizationNotification::where('organization_name', 'like', '%ใหญ่%')->first();
$hyN = OrganizationNotification::where('organization_name', 'like', '%หาดใหญ่%')->first();

echo "HQ Source: " . ($hqN ? $hqN->telegram_chat_id : 'Not Found') . "\n";
echo "HY Source: " . ($hyN ? $hyN->telegram_chat_id : 'Not Found') . "\n";
