<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'Approved' and 'Received' to status ENUM
     */
    public function up(): void
    {
        // MySQL requires ALTER to modify ENUM values
        DB::statement("ALTER TABLE other_requests MODIFY COLUMN status ENUM('Pending', 'Approved', 'In Progress', 'Completed', 'Received', 'Rejected') DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original ENUM (must ensure no data uses new values)
        DB::statement("ALTER TABLE other_requests MODIFY COLUMN status ENUM('Pending', 'In Progress', 'Completed', 'Rejected') DEFAULT 'Pending'");
    }
};
