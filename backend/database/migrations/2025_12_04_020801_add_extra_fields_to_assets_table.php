<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('department')->nullable()->after('organization');
            $table->string('assigned_to')->nullable()->after('assigned_to_id');
            $table->string('assigned_to_email')->nullable()->after('assigned_to');
            $table->string('assigned_to_phone')->nullable()->after('assigned_to_email');
            $table->string('ip_address')->nullable()->after('location');
            $table->string('mac_address')->nullable()->after('ip_address');
            $table->string('license_key')->nullable()->after('mac_address');
            $table->string('license_type')->nullable()->after('license_key');
            $table->date('expiry_date')->nullable()->after('warranty_expiry');
            $table->integer('total_licenses')->nullable()->after('expiry_date');
            $table->integer('used_licenses')->nullable()->after('total_licenses');
            $table->date('start_date')->nullable()->after('purchase_date');
            $table->string('qr_code')->nullable()->after('used_licenses');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'department',
                'assigned_to',
                'assigned_to_email',
                'assigned_to_phone',
                'ip_address',
                'mac_address',
                'license_key',
                'license_type',
                'expiry_date',
                'total_licenses',
                'used_licenses',
                'start_date',
                'qr_code',
            ]);
        });
    }
};
