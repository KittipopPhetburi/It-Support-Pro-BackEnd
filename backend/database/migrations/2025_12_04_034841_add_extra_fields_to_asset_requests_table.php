<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->string('request_type')->default('Requisition')->after('requester_id');
            $table->foreignId('asset_id')->nullable()->after('asset_type')
                ->constrained('assets')->nullOnDelete();
            $table->string('department')->nullable()->after('department_id');
            $table->string('reason')->nullable()->after('justification');
            $table->string('requester_name')->nullable()->after('requester_id');
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejected_by')->nullable();
            $table->string('reject_reason')->nullable();
            $table->timestamp('received_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
            $table->dropColumn([
                'request_type',
                'asset_id', 
                'department',
                'reason',
                'requester_name',
                'approved_at',
                'approved_by',
                'rejected_at',
                'rejected_by',
                'reject_reason',
                'received_at'
            ]);
        });
    }
};
