<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Asset;

$asset = Asset::where('name', 'like', '%Hadyai%')->first();
if ($asset) {
    echo "ID: " . $asset->id . "\n";
    echo "Name: " . $asset->name . "\n";
    echo "Status: '" . $asset->status . "'\n";
    echo "Organization: '" . $asset->organization . "'\n";
    echo "Branch ID: " . ($asset->branch_id ?? 'NULL') . "\n";
    echo "Quantity: " . $asset->quantity . "\n";
    // Check if there is an accessor for available_quantity if it exists on model, otherwise calculate it manually to see logic
    // Usually it's raw column or no column for individual items
    
    // Check if it's assigned
    echo "Assigned To: " . ($asset->assigned_to ?? 'NULL') . "\n";
} else {
    echo "Asset not found\n";
}
