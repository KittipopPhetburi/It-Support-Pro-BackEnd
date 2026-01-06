<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Branch;

$hq = Branch::where('name', 'สำนักงานใหญ่')->first();
if ($hq) {
    if (empty($hq->telegram_chat_id)) {
        $hq->telegram_chat_id = '-5190545474';
        $hq->notification_config = [
            'incident' => true,
            'asset_request' => true,
            'other_request' => true
        ];
        $hq->save();
        echo "Updated HQ Chat ID to -5190545474\n";
    } else {
        echo "jHQ already has Chat ID: " . $hq->telegram_chat_id . "\n";
    }
} else {
    echo "Branch 'สำนักงานใหญ่' not found!\n";
}
