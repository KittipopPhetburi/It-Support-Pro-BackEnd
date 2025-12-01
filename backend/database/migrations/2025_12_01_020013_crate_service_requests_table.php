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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_id')
                ->constrained('service_catalog_items')
                ->cascadeOnDelete();

            $table->string('service_name'); // snapshot ชื่อ service ตอนสร้างคำขอ

            $table->foreignId('requester_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('status', ['Pending', 'Approved', 'In Progress', 'Completed', 'Rejected'])
                  ->default('Pending');

            $table->timestamp('request_date')->nullable();
            $table->timestamp('completion_date')->nullable();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->nullOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->string('organization')->nullable();

            $table->timestamps();
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
        //
    }
};
