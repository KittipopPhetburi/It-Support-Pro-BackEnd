<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Incident;
use App\Models\Branch;

$latest = Incident::latest()->first();
echo "Latest Incident ID: " . $latest->id . "\n";
echo "Incident Branch ID: " . $latest->branch_id . "\n";

if ($latest->branch) {
    echo "Incident Branch Name: " . $latest->branch->name . "\n";
    echo "Incident Branch Chat ID: " . $latest->branch->telegram_chat_id . "\n";
} else {
    echo "Incident Branch: NULL\n";
}

$hq = Branch::where('name', 'สำนักงานใหญ่')->first();
echo "HQ DB Chat ID: " . $hq->telegram_chat_id . "\n";
