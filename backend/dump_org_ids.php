<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrganizationNotification;

$notifs = OrganizationNotification::all();
foreach ($notifs as $n) {
    echo "Name: " . $n->organization_name . " | Chat ID: " . $n->telegram_chat_id . "\n";
}
