<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Drop the unique index first
            $table->dropUnique(['serial_number']);
            
            // Change serial_number to TEXT to support multiple serials
            $table->text('serial_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Revert back to string (might fail if data is too long, but this is rollback)
            $table->string('serial_number')->nullable()->change();
            
            // Re-add unique index
            $table->unique('serial_number');
        });
    }
};
