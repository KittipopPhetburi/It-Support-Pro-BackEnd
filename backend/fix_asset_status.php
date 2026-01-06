<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Asset;

$asset = Asset::where('name', 'like', '%Hadyai%')->first();
if ($asset) {
    echo "Old Status: " . $asset->status . "\n";
    $asset->status = 'Available';
    $asset->save();
    echo "New Status: " . $asset->status . "\n";
}
