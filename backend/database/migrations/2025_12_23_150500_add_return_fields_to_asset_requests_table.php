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
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->timestamp('borrow_date')->nullable()->after('request_date');
            $table->timestamp('due_date')->nullable()->after('borrow_date');
            $table->boolean('is_returned')->default(false)->after('status');
            $table->timestamp('return_date')->nullable()->after('is_returned');
            $table->string('return_condition')->nullable()->after('return_date');
            $table->text('return_notes')->nullable()->after('return_condition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->dropColumn([
                'borrow_date',
                'due_date',
                'is_returned',
                'return_date',
                'return_condition',
                'return_notes',
            ]);
        });
    }
};
