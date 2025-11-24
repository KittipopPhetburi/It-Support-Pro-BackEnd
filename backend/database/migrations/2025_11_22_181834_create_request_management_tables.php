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
        Schema::create('asset_request_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('asset_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('requester_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('status_id')->constrained('asset_request_statuses');
            $table->string('request_type'); // new, replacement, upgrade
            $table->text('reason')->nullable();
            $table->date('requested_date')->nullable();
            $table->date('approved_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_request_id')->constrained('asset_requests')->onDelete('cascade');
            $table->foreignId('asset_category_id')->constrained('asset_categories');
            $table->integer('quantity');
            $table->text('specification')->nullable();
            $table->decimal('budget_per_item', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('other_request_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('other_request_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('other_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('requester_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('handler_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->constrained('other_request_categories');
            $table->foreignId('status_id')->constrained('other_request_statuses');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('requested_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_requests');
        Schema::dropIfExists('other_request_statuses');
        Schema::dropIfExists('other_request_categories');
        Schema::dropIfExists('asset_request_items');
        Schema::dropIfExists('asset_requests');
        Schema::dropIfExists('asset_request_statuses');
    }
};
