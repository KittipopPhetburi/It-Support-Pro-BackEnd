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
        Schema::table('service_requests', function (Blueprint $table) {
            $table->text('description')->nullable()->after('service_name');
            $table->string('requested_by')->nullable()->after('requester_id');
            $table->foreignId('approved_by_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by_id');
            $table->text('rejected_reason')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by_id']);
            $table->dropColumn(['description', 'requested_by', 'approved_by_id', 'approved_at', 'rejected_reason']);
        });
    }
};
