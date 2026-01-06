<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$notifs = App\Models\OrganizationNotification::all();
foreach ($notifs as $n) {
    if (strpos($n->organization_name, 'ใหญ่') !== false) {
        echo "Found: " . $n->organization_name . " | ChatID: " . $n->telegram_chat_id . "\n";
    }
}
