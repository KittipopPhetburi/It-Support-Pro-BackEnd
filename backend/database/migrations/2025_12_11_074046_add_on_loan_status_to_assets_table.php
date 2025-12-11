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
        // Change ENUM to include 'On Loan' status
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('Available', 'In Use', 'Maintenance', 'Retired', 'On Loan') DEFAULT 'Available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM values
        // First update any 'On Loan' to 'Available'
        DB::table('assets')->where('status', 'On Loan')->update(['status' => 'Available']);
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('Available', 'In Use', 'Maintenance', 'Retired') DEFAULT 'Available'");
    }
};
