<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Branch;

$branches = Branch::whereIn('name', ['สำนักงานใหญ่', 'สาขาหาดใหญ่'])->get();
foreach ($branches as $b) {
    echo "Branch: {$b->name} | Chat ID: {$b->telegram_chat_id}\n";
}
