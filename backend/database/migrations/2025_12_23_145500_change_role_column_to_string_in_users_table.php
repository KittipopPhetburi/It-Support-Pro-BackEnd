<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to ensure it works without doctrine/dbal and avoids complex enum handling issues
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(255) NOT NULL DEFAULT 'User'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-creating the ENUM constraint (be careful as this might fail if data violates it)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Admin', 'Technician', 'Helpdesk', 'Purchase', 'User') NOT NULL DEFAULT 'User'");
    }
};
