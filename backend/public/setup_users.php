<?php

// 1. Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Boot the Console Kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Role;
use App\Models\SystemSetting;
use Database\Seeders\CreateEssentialUsersSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\NotificationSettingsSeeder;
use Database\Seeders\RefreshOperationalDataSeeder;
use Database\Seeders\RoleSeeder;

// Fix for hosts where composer dump-autoload cannot be run: Explicitly require seeders
require_once __DIR__ . '/../database/seeders/CreateEssentialUsersSeeder.php';
require_once __DIR__ . '/../database/seeders/SystemSettingsSeeder.php';
require_once __DIR__ . '/../database/seeders/NotificationSettingsSeeder.php';
require_once __DIR__ . '/../database/seeders/RefreshOperationalDataSeeder.php';
require_once __DIR__ . '/../database/seeders/RoleSeeder.php';
require_once __DIR__ . '/../database/seeders/PermissionSeeder.php';

// Styles
$style = "
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f3f4f6; padding: 20px; max-width: 800px; margin: 0 auto; }
    .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .status-ok { color: green; font-weight: bold; }
    .status-err { color: red; font-weight: bold; }
    .btn { display: inline-block; padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; font-size: 14px; margin-right: 10px; }
    .btn:hover { background: #2563eb; }
    .btn-secondary { background: #6b7280; }
    .btn-secondary:hover { background: #4b5563; }
    .btn-danger { background: #ef4444; }
    .btn-danger:hover { background: #dc2626; }
    h2 { margin-top: 0; color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; }
    pre { background: #1f2937; color: #f3f4f6; padding: 15px; border-radius: 5px; overflow-x: auto; }
";

echo "<html><head><title>IT Support Pro Setup</title><style>$style</style></head><body>";

// --- Action Handlers ---

use Database\Seeders\PermissionSeeder; // Added
use App\Models\Menu; // Added

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // DB Config Update (if provided)
    if (isset($_POST['db_host'])) {
        config(['database.connections.mysql.host' => $_POST['db_host']]);
        config(['database.connections.mysql.database' => $_POST['db_name']]);
        config(['database.connections.mysql.username' => $_POST['db_user']]);
        config(['database.connections.mysql.password' => $_POST['db_pass']]);
        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    echo "<div class='card'>";
    echo "<h3>Result for: $action</h3>";
    
    try {
        if ($action === 'migrate') {
            Artisan::call('migrate', ['--force' => true]);
            echo "<pre>" . Artisan::output() . "</pre>";
            echo "<p class='status-ok'>Migrations completed.</p>";
        }
        elseif ($action === 'seed_users') {
            $seeder = new CreateEssentialUsersSeeder();
            $seeder->run();
            echo "<p class='status-ok'>Essential users seeded successfully.</p>";
        }
        elseif ($action === 'seed_settings') {
            $seeder1 = new SystemSettingsSeeder();
            $seeder1->run();
            $seeder2 = new NotificationSettingsSeeder();
            $seeder2->run();
            $seeder3 = new RoleSeeder(); 
            $seeder3->run();
            $seeder4 = new PermissionSeeder(); // Added
            $seeder4->run();
            echo "<p class='status-ok'>System, Settings, Roles & Permissions seeded successfully.</p>";
        }
        elseif ($action === 'seed_roles') {
            $seeder = new RoleSeeder();
            $seeder->run();
            $seeder2 = new PermissionSeeder(); // Added
            $seeder2->run();
            echo "<p class='status-ok'>Roles & Permissions seeded successfully.</p>";
        }
        elseif ($action === 'seed_demo') {
            $seeder = new RefreshOperationalDataSeeder();
            $seeder->run();
            echo "<p class='status-ok'>Demo operational data seeded successfully.</p>";
        }
    } catch (\Exception $e) {
        echo "<p class='status-err'>Error: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
    echo "<a href='setup_users.php' class='btn btn-secondary'>Back to Dashboard</a>";
    echo "</div>";
    
    // Stop rendering the dashboard if an action result is shown
    echo "</body></html>";
    exit;
}

// --- Dashboard ---

echo "<h1>IT Support Pro - Setup Utility</h1>";
echo "<div style='color:red; margin-bottom: 20px; font-weight:bold;'>⚠️ Security Warning: Delete this file after initial setup!</div>";

// 1. Check Database Connection
echo "<div class='card'>";
echo "<h2>1. Database Connection</h2>";
try {
    DB::connection()->getPdo();
    echo "<p>Status: <span class='status-ok'>Connected</span></p>";
    echo "<p>Database: <strong>" . config('database.connections.mysql.database') . "</strong></p>";
} catch (\Exception $e) {
    echo "<p>Status: <span class='status-err'>Connection Failed</span></p>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    
    // Connection Form
    $currentDb = config('database.connections.mysql.database');
    $currentUser = config('database.connections.mysql.username');
    echo "<form method='POST'>";
    echo "<div style='margin-bottom:10px'><label>DB Host:</label><br><input type='text' name='db_host' value='127.0.0.1' style='width:100%; padding:8px;'></div>";
    echo "<div style='margin-bottom:10px'><label>Database Name:</label><br><input type='text' name='db_name' value='{$currentDb}' style='width:100%; padding:8px;'></div>";
    echo "<div style='margin-bottom:10px'><label>Username:</label><br><input type='text' name='db_user' value='{$currentUser}' style='width:100%; padding:8px;'></div>";
    echo "<div style='margin-bottom:10px'><label>Password:</label><br><input type='password' name='db_pass' style='width:100%; padding:8px;'></div>";
    echo "<input type='hidden' name='action' value='check_connection'>"; // Just reloads
    echo "<button type='submit' class='btn'>Connect</button>";
    echo "</form>";
    echo "</div></body></html>";
    exit; // Stop here if no DB
}
echo "</div>";

// 2. Check Tables
echo "<div class='card'>";
echo "<h2>2. Database Structure (Migrations)</h2>";
$hasUsersTable = Schema::hasTable('users');
$hasSettingsTable = Schema::hasTable('system_settings');
$hasRolesTable = Schema::hasTable('roles');

if ($hasUsersTable) {
    echo "<p>Tables: <span class='status-ok'>Found (users table exists)</span></p>";
} else {
    echo "<p>Tables: <span class='status-err'>Missing</span></p>";
    echo "<p>You need to run migrations to create the database structure.</p>";
    echo "<form method='POST'>";
    echo "<input type='hidden' name='action' value='migrate'>";
    echo "<button type='submit' class='btn'>Run Migrations</button>";
    echo "</form>";
}
echo "</div>";

if (!$hasUsersTable) {
    echo "</body></html>";
    exit; // Stop here if no tables
}

// 3. Check Data
echo "<div class='card'>";
echo "<h2>3. Data Seeding</h2>";

// 3.1 Users
$userCount = User::count();
echo "<div style='margin-bottom: 20px;'>";
echo "<strong>Essential Users: </strong>";
if ($userCount > 0) {
    echo "<span class='status-ok'>Found ($userCount users)</span>";
} else {
    echo "<span class='status-err'>Missing</span>";
    echo "<br><small>Required for login (Admin, Tech, etc.)</small>";
    echo "<form method='POST' style='margin-top:10px;'>";
    echo "<input type='hidden' name='action' value='seed_users'>";
    echo "<button type='submit' class='btn'>Seed Essential Users</button>";
    echo "</form>";
}
echo "</div>";

// 3.2 Roles & Permissions
$roleCount = $hasRolesTable ? Role::count() : 0;
// Check permissions count merely to give a visual clue
$permCount = Schema::hasTable('role_menu_permissions') ? DB::table('role_menu_permissions')->count() : 0;

echo "<div style='margin-bottom: 20px;'>";
echo "<strong>User Roles & Permissions: </strong>";
if ($roleCount > 0 && $permCount > 0) {
    echo "<span class='status-ok'>Found ($roleCount roles, $permCount permissions)</span>";
} else {
    echo "<span class='status-err'>Missing / Incomplete</span>";
    echo "<br><small>Admin, Technician roles + Menu Access</small>";
    echo "<form method='POST' style='margin-top:10px;'>";
    echo "<input type='hidden' name='action' value='seed_roles'>";
    echo "<button type='submit' class='btn'>Seed Roles & Permissions</button>";
    echo "</form>";
}
echo "</div>";

// 3.3 Settings
$settingsCount = $hasSettingsTable ? SystemSetting::count() : 0;
echo "<div style='margin-bottom: 20px;'>";
echo "<strong>System Configuration: </strong>";
if ($settingsCount > 0) {
    echo "<span class='status-ok'>Found ($settingsCount settings)</span>";
} else {
    echo "<span class='status-err'>Missing</span>";
    echo "<br><small>Default values for SMTP, SLA, etc.</small>";
    echo "<form method='POST' style='margin-top:10px;'>";
    echo "<input type='hidden' name='action' value='seed_settings'>";
    echo "<button type='submit' class='btn'>Seed All Defaults (Recommended)</button>";
    echo "</form>";
}
echo "</div>";

// 3.4 Demo Data
echo "<div>";
echo "<strong>Operational Data (Demo): </strong>";
echo "<br><small>Sample Assets, Incidents for testing (Optional)</small>";
echo "<form method='POST' style='margin-top:10px;'>";
echo "<input type='hidden' name='action' value='seed_demo'>";
echo "<button type='submit' class='btn btn-secondary'>Seed Demo Data</button>";
echo "</form>";
echo "</div>";

echo "</div>"; // End Card 3

echo "<p style='text-align:center;'><a href='/' class='btn'>Go to Application Homepage</a></p>";

echo "</body></html>";
