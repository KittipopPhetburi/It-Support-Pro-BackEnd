<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'user@example.com')->first();
if ($user) {
    echo "User: " . $user->name . "\n";
    echo "Branch ID: " . $user->branch_id . "\n";
    if ($user->branch) {
        echo "Branch Name: " . $user->branch->name . "\n";
    } else {
        echo "Branch Relation: NULL\n";
    }
} else {
    echo "User not found\n";
}
