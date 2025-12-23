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
        Schema::create('pm_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->enum('frequency', ['Weekly', 'Monthly', 'Quarterly', 'Semi-Annually', 'Annually'])->default('Monthly');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            $table->date('scheduled_date');
            $table->date('next_scheduled_date')->nullable();
            $table->enum('status', ['Scheduled', 'In Progress', 'Completed', 'Overdue', 'Cancelled'])->default('Scheduled');
            $table->enum('check_result', ['Pass', 'Fail', 'NeedsRepair'])->nullable();
            $table->text('notes')->nullable();
            $table->json('issues_found')->nullable();
            $table->text('recommendations')->nullable();
            $table->json('images')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for frequently queried columns
            $table->index('status');
            $table->index('scheduled_date');
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pm_schedules');
    }
};
