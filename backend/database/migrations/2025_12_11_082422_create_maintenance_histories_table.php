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
        Schema::create('maintenance_histories', function (Blueprint $table) {
            $table->id();
            
            // Link to Asset
            $table->foreignId('asset_id')
                ->constrained('assets')
                ->cascadeOnDelete();
            
            // Link to Incident (optional)
            $table->foreignId('incident_id')
                ->nullable()
                ->constrained('incidents')
                ->nullOnDelete();
            
            // Repair Details
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('repair_status')->nullable();
            
            // Technician Info
            $table->foreignId('technician_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('technician_name')->nullable();
            
            // Dates
            $table->dateTime('start_date')->nullable();
            $table->dateTime('completion_date')->nullable();
            
            // Cost
            $table->boolean('has_cost')->default(false);
            $table->decimal('cost', 10, 2)->nullable();
            
            // Replacement
            $table->string('replacement_equipment')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_histories');
    }
};
