<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Branch;

$hq = Branch::where('name', 'สำนักงานใหญ่')->first();
if ($hq) {
    echo "Old HQ Chat ID: " . $hq->telegram_chat_id . "\n";
    $hq->telegram_chat_id = '-1003409917470';
    $hq->save();
    echo "New HQ Chat ID: " . $hq->telegram_chat_id . "\n";
} else {
    echo "HQ Branch not found\n";
}

$hy = Branch::where('name', 'สาขาหาดใหญ่')->first();
if ($hy) {
    echo "Hat Yai Chat ID: " . $hy->telegram_chat_id . "\n";
    // Optional: Ensure Hat Yai has the ID that was previously on HQ if that was indeed Hat Yai's ID
    if ($hy->telegram_chat_id == null) {
        $hy->telegram_chat_id = '-5190545474';
        $hy->save();
        echo "Updated Hat Yai Chat ID to -5190545474\n";
    }
}
