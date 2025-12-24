<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('project_value', 15, 2)->default(0);
            $table->foreignId('project_manager_id')->constrained('users')->onDelete('cascade');
            $table->string('organization')->nullable();
            $table->string('department')->nullable();
            $table->text('description')->nullable();
            $table->string('contract_file_name')->nullable();
            $table->string('contract_file_path')->nullable();
            $table->string('tor_file_name')->nullable();
            $table->string('tor_file_path')->nullable();
            $table->enum('status', ['Planning', 'In Progress', 'Completed', 'Cancelled'])->default('Planning');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_projects');
    }
};
