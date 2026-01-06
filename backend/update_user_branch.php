<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Branch;

$user = User::where('email', 'user@example.com')->first();
$hq = Branch::where('name', 'สำนักงานใหญ่')->first();

if ($user && $hq) {
    echo "Updating user {$user->name} from Branch ID " . ($user->branch_id ?? 'NULL') . " to " . $hq->id . " (" . $hq->name . ")...\n";
    $user->branch_id = $hq->id;
    $user->save();
    echo "Update Complete!\n";
    
    // Verify
    $user->refresh();
    echo "Current Branch: " . $user->branch->name . " (ID: " . $user->branch_id . ")\n";
} else {
    echo "User or HQ Branch not found!\n";
}
