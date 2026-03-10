<?php

/**
 * Laravel Migration Runner
 * 
 * This script allows you to run 'php artisan migrate' directly from your browser.
 * Only use this if you do not have SSH access to your server.
 * 
 * IMPORTANT: DELETE THIS FILE AFTER USE FOR SECURITY!
 */

// 1. Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Boot the Console Kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Migration Runner</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 40px auto; padding: 0 20px; background: #f4f7f6; }
        .container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .btn { display: inline-block; background: #3498db; color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none; border: none; font-size: 16px; cursor: pointer; margin-right: 10px; }
        .btn-green { background: #27ae60; }
        .btn-red { background: #e74c3c; }
        .btn-orange { background: #f39c12; }
        .output { background: #2c3e50; color: #ecf0f1; padding: 20px; border-radius: 5px; margin-top: 20px; white-space: pre-wrap; font-family: "Courier New", Courier, monospace; overflow-x: auto; }
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert-warning { background: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
        .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Laravel Migration Runner</h1>
        
        <div class="alert alert-warning">
            <strong>Security Warning:</strong> This file exposes sensitive database operations. 
            <br><strong>MUST DELETE THIS FILE</strong> from the server immediately after successful migration.
        </div>

        <?php
        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            echo "<div class='alert alert-success'>✅ Connected to database: <strong>$dbName</strong></div>";
        } catch (\Exception $e) {
            echo "<div class='alert alert-danger'>❌ Database connection failed: " . $e->getMessage() . "</div>";
        }
        ?>

        <form method="POST">
            <button type="submit" name="action" value="migrate" class="btn btn-green">Run Migrate</button>
            <button type="submit" name="action" value="status" class="btn">Migration Status</button>
            <button type="submit" name="action" value="rollback" class="btn btn-orange" onclick="return confirm('Are you sure you want to rollback the last migration?')">Rollback Last</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];
            echo "<h2>Running: artisan $action</h2>";
            echo "<div class='output'>";
            
            try {
                switch ($action) {
                    case 'migrate':
                        Artisan::call('migrate', ['--force' => true]);
                        break;
                    case 'status':
                        Artisan::call('migrate:status');
                        break;
                    case 'rollback':
                        Artisan::call('migrate:rollback', ['--force' => true]);
                        break;
                    default:
                        echo "Unknown action.";
                }
                echo Artisan::output();
            } catch (\Exception $e) {
                echo "Error: " . $e->getMessage();
            }
            
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>
