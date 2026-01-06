<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrganizationNotification;

$items = OrganizationNotification::all();
foreach ($items as $item) {
    echo sprintf("[%s] -> [%s]\n", trim($item->organization_name), trim($item->telegram_chat_id));
}
