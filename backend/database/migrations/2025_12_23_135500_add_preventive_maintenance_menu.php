<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add Preventive Maintenance menu
        DB::table('menus')->insert([
            'key' => 'preventive_maintenance',
            'name' => 'Preventive Maintenance',
            'group' => 'การจัดการระบบ',
            'sort_order' => 8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('menus')->where('key', 'preventive_maintenance')->delete();
    }
};
