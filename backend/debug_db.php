<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Branch;
use App\Models\OrganizationNotification;

$branches = Branch::all()->toArray();
echo "Branches:\n";
print_r($branches);

$notifs = OrganizationNotification::all()->toArray();
echo "\nNotifications:\n";
print_r($notifs);
