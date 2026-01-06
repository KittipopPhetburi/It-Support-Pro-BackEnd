<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Asset;

$assets = Asset::where('name', 'like', '%Hadyai%')->get();
echo "Found " . $assets->count() . " assets matching 'Hadyai':\n";
foreach ($assets as $asset) {
    echo "ID: " . $asset->id . " | Name: " . $asset->name . " | Status: " . $asset->status . " | BranchId: " . ($asset->branch_id ?? 'NULL') . " | Org: " . $asset->organization . "\n";
}
