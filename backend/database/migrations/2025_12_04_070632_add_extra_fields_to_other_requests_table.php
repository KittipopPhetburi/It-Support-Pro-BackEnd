<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('other_requests', function (Blueprint $table) {
            $table->string('item_name')->nullable()->after('title');
            $table->string('item_type')->nullable()->after('item_name');
            $table->string('request_type')->nullable()->after('item_type');
            $table->integer('quantity')->default(1)->after('request_type');
            $table->string('unit')->default('piece')->after('quantity');
            $table->string('requester_name')->nullable()->after('requester_id');
            $table->text('reason')->nullable()->after('unit');
            $table->string('approved_by')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('rejected_by')->nullable()->after('approved_at');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('reject_reason')->nullable()->after('rejected_at');
            $table->string('completed_by')->nullable()->after('reject_reason');
            $table->timestamp('completed_at')->nullable()->after('completed_by');
            $table->timestamp('received_at')->nullable()->after('completed_at');
            $table->string('brand')->nullable()->after('received_at');
            $table->string('model')->nullable()->after('brand');
        });
    }

    public function down(): void
    {
        Schema::table('other_requests', function (Blueprint $table) {
            $table->dropColumn([
                'item_name', 'item_type', 'request_type', 'quantity', 'unit',
                'requester_name', 'reason', 'approved_by', 'approved_at',
                'rejected_by', 'rejected_at', 'reject_reason', 'completed_by',
                'completed_at', 'received_at', 'brand', 'model'
            ]);
        });
    }
};