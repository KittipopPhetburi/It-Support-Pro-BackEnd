<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE asset_requests MODIFY COLUMN status ENUM('Pending', 'Approved', 'Rejected', 'Fulfilled', 'Received', 'Cancelled', 'Returned') DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE asset_requests MODIFY COLUMN status ENUM('Pending', 'Approved', 'Rejected', 'Fulfilled', 'Received', 'Cancelled') DEFAULT 'Pending'");
    }
};
