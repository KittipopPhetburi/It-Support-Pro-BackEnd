<?php

// 1. Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Boot the Console Kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

// Handle Form Submission for custom DB credentials
if (isset($_POST['run_seeder'])) {
    if (isset($_POST['db_host'])) {
        config(['database.connections.mysql.host' => $_POST['db_host']]);
        config(['database.connections.mysql.database' => $_POST['db_name']]);
        config(['database.connections.mysql.username' => $_POST['db_user']]);
        config(['database.connections.mysql.password' => $_POST['db_pass']]);
        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    echo "<h3>Seeding Operational Data...</h3>";
    try {
        // Run specific seeder class
        Artisan::call('db:seed', ['--class' => 'RefreshOperationalDataSeeder', '--force' => true]);
        echo "<pre>" . Artisan::output() . "</pre>";
        echo "<h3 style='color:green'>✅ Data Seeding Completed!</h3>";
        echo "<p>Asset and Incident data has been populated.</p>";
        echo "<p><a href='/'>Go to Homepage</a></p>";
    } catch (\Exception $e) {
        echo "<h3 style='color:red'>❌ Seeding Failed: " . $e->getMessage() . "</h3>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}

echo "<h1>Setup Operational Data</h1>";
echo "<p>This will clear existing Incidents/Assets and re-seed sample data.</p>";

try {
    // Check connection
    DB::connection()->getPdo();
    $currentDb = config('database.connections.mysql.database');
    
    echo "<p>Connected to Database: <strong>{$currentDb}</strong></p>";
    
    echo "<form method='POST'>";
    echo "<input type='hidden' name='run_seeder' value='true'>";
    echo "<button type='submit' style='background:green; color:white; border:none; padding:15px 30px; font-size:16px; cursor:pointer; border-radius:5px;'>Populate Data Now</button>";
    echo "</form>";

} catch (\Exception $e) {
    echo "<h2 style='color:red'>❌ Connection Error</h2>";
    
    $currentDb = config('database.connections.mysql.database');
    $currentUser = config('database.connections.mysql.username');

    if (strpos($e->getMessage(), 'Access denied') !== false || strpos($e->getMessage(), 'SQLSTATE[HY000]') !== false || strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "<div style='background:#f9f9f9; padding:20px; border:1px solid #ccc; max-width:500px;'>";
        echo "<h3>🔧 Database Credentials</h3>";
        echo "<form method='POST'>";
        echo "<div style='margin-bottom:10px'><label>DB Host:</label><br><input type='text' name='db_host' value='127.0.0.1' style='width:100%; padding:5px;'></div>";
        echo "<div style='margin-bottom:10px'><label>Database Name:</label><br><input type='text' name='db_name' value='{$currentDb}' style='width:100%; padding:5px;'></div>";
        echo "<div style='margin-bottom:10px'><label>Username:</label><br><input type='text' name='db_user' value='{$currentUser}' style='width:100%; padding:5px;'></div>";
        echo "<div style='margin-bottom:10px'><label>Password:</label><br><input type='text' name='db_pass' placeholder='Enter DB password' style='width:100%; padding:5px;'></div>";
        echo "<input type='hidden' name='run_seeder' value='true'>";
        echo "<button type='submit' style='background:blue; color:white; border:none; padding:10px 20px; cursor:pointer;'>Connect & Seed Data</button>";
        echo "</form>";
        echo "</div>";
    } else {
        echo "<pre>" . $e->getMessage() . "</pre>";
    }
}

echo "<br><br><p style='color:red; font-weight:bold;'>IMPORTANT: Please delete this file (setup_data.php) after use!</p>";
