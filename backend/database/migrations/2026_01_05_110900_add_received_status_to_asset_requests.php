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
        // Modify ENUM to add 'Received' status
        DB::statement("ALTER TABLE asset_requests MODIFY status ENUM('Pending', 'Approved', 'Rejected', 'Fulfilled', 'Received') DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert ENUM back to original values
        DB::statement("ALTER TABLE asset_requests MODIFY status ENUM('Pending', 'Approved', 'Rejected', 'Fulfilled') DEFAULT 'Pending'");
    }
};
