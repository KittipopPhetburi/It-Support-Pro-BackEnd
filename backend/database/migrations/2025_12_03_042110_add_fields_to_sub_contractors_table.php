<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_contractors', function (Blueprint $table) {
            $table->string('specialty')->nullable()->after('phone');
            $table->string('province')->nullable()->after('specialty');
            $table->string('bank_name')->nullable()->after('province');
            $table->string('bank_account_name')->nullable()->after('bank_name');
            $table->string('bank_account_number')->nullable()->after('bank_account_name');
        });
    }

    public function down(): void
    {
        Schema::table('sub_contractors', function (Blueprint $table) {
            $table->dropColumn(['specialty', 'province', 'bank_name', 'bank_account_name', 'bank_account_number']);
        });
    }
};
