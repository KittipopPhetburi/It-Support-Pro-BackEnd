<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'code')) {
                $table->string('code', 50)->nullable()->after('id');
            }
            if (!Schema::hasColumn('branches', 'province')) {
                $table->string('province')->nullable()->after('address');
            }
            if (!Schema::hasColumn('branches', 'phone')) {
                $table->string('phone', 50)->nullable()->after('province');
            }
            if (!Schema::hasColumn('branches', 'status')) {
                $table->enum('status', ['Active', 'Inactive'])->default('Active')->after('organization');
            }
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['code', 'province', 'phone', 'status']);
        });
    }
};
