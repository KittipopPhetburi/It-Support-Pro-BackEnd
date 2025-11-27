<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adding fields to match frontend types.ts
     */
    public function up(): void
    {
        // Add missing fields to incidents table
        Schema::table('incidents', function (Blueprint $table) {
            // Contact info
            $table->string('contact_method')->nullable()->after('contact_phone');
            $table->string('location')->nullable()->after('location_text');
            
            // Asset info
            $table->foreignId('asset_id')->nullable()->after('department_id')->constrained('assets')->nullOnDelete();
            $table->string('asset_name')->nullable();
            $table->string('asset_brand')->nullable();
            $table->string('asset_model')->nullable();
            $table->string('asset_serial_number')->nullable();
            $table->string('asset_inventory_number')->nullable();
            $table->boolean('is_custom_asset')->default(false);
            $table->string('equipment_type')->nullable();
            $table->string('operating_system')->nullable();
            
            // Repair info
            $table->dateTime('start_repair_date')->nullable();
            $table->dateTime('completion_date')->nullable();
            $table->text('repair_details')->nullable();
            $table->string('repair_status')->nullable();
            $table->string('replacement_equipment')->nullable();
            $table->boolean('has_additional_cost')->default(false);
            $table->decimal('additional_cost', 10, 2)->nullable();
            $table->text('technician_signature')->nullable();
            $table->text('customer_signature')->nullable();
            
            // Satisfaction info
            $table->integer('satisfaction_rating')->nullable();
            $table->text('satisfaction_comment')->nullable();
            $table->dateTime('satisfaction_date')->nullable();
            
            // Subcategory
            $table->string('subcategory')->nullable();
        });

        // Add missing fields to assets table  
        Schema::table('assets', function (Blueprint $table) {
            $table->string('inventory_number')->nullable()->after('serial_number');
            $table->string('type')->nullable()->after('name');
            $table->string('location')->nullable()->after('department_id');
        });

        // Add fields to kb_articles table to match types.ts
        Schema::table('kb_articles', function (Blueprint $table) {
            $table->string('author')->nullable()->after('content');
            $table->json('tags')->nullable()->after('author');
            $table->integer('views')->default(0)->after('is_published');
            $table->integer('helpful')->default(0)->after('views');
            $table->integer('not_helpful')->default(0)->after('helpful');
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
            $table->dropColumn([
                'contact_method',
                'location',
                'asset_id',
                'asset_name',
                'asset_brand',
                'asset_model',
                'asset_serial_number',
                'asset_inventory_number',
                'is_custom_asset',
                'equipment_type',
                'operating_system',
                'start_repair_date',
                'completion_date',
                'repair_details',
                'repair_status',
                'replacement_equipment',
                'has_additional_cost',
                'additional_cost',
                'technician_signature',
                'customer_signature',
                'satisfaction_rating',
                'satisfaction_comment',
                'satisfaction_date',
                'subcategory',
            ]);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['inventory_number', 'type', 'location']);
        });

        Schema::table('kb_articles', function (Blueprint $table) {
            $table->dropColumn(['author', 'tags', 'views', 'helpful', 'not_helpful']);
        });
    }
};
