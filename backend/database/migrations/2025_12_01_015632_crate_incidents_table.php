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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('priority', ['Low', 'Medium', 'High', 'Critical'])
                  ->default('Low');

            $table->enum('status', ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'])
                  ->default('Open');

            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();

            // ผู้เกี่ยวข้อง
            $table->foreignId('requester_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('reported_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('assignee_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // เวลา
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            // โครงสร้างองค์กร
            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->nullOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->string('organization')->nullable();

            // ข้อมูลติดต่อ
            $table->string('contact_method')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('location')->nullable();

            // ข้อมูล Asset
            $table->foreignId('asset_id')
                ->nullable()
                ->constrained('assets')
                ->nullOnDelete();

            $table->string('asset_name')->nullable();
            $table->string('asset_brand')->nullable();
            $table->string('asset_model')->nullable();
            $table->string('asset_serial_number')->nullable();
            $table->string('asset_inventory_number')->nullable();
            $table->boolean('is_custom_asset')->default(false);
            $table->string('equipment_type')->nullable();
            $table->string('operating_system')->nullable();

            // ข้อมูลซ่อม
            $table->timestamp('start_repair_date')->nullable();
            $table->timestamp('completion_date')->nullable();
            $table->text('repair_details')->nullable();
            $table->string('repair_status')->nullable();
            $table->string('replacement_equipment')->nullable();
            $table->boolean('has_additional_cost')->default(false);
            $table->decimal('additional_cost', 12, 2)->nullable();

            // ลายเซ็น / ความพึงพอใจ
            $table->string('technician_signature')->nullable();
            $table->string('customer_signature')->nullable();

            $table->unsignedTinyInteger('satisfaction_rating')->nullable();
            $table->text('satisfaction_comment')->nullable();
            $table->timestamp('satisfaction_date')->nullable();

            $table->timestamps();
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
        //
    }
};
