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
            $table->string('project_code')->unique(); // PRJ-0001, PRJ-0002
            $table->string('name');
            $table->string('organization')->nullable();
            $table->string('department')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('budget', 15, 2)->default(0);
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->enum('status', ['Planning', 'In Progress', 'Completed', 'Cancelled'])->default('Planning');
            $table->string('contract_file')->nullable();
            $table->string('tor_file')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_projects');
    }
};
