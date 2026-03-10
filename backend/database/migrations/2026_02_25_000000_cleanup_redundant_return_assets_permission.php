<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Redundant menu keys to remove
        $redundantKeys = ['return_assets', 'assets'];

        foreach ($redundantKeys as $key) {
            $menu = DB::table('menus')->where('key', $key)->first();

            if ($menu) {
                // Delete related permissions first (foreign key constraint safety)
                DB::table('role_menu_permissions')->where('menu_id', $menu->id)->delete();
                
                // Delete the redundant menu entry
                DB::table('menus')->where('id', $menu->id)->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-adding the redundant entry is not usually necessary as the seeder can re-create it if needed,
        // but for migration symmetry:
        // (Not implemented as it's a cleanup migration)
    }
};
