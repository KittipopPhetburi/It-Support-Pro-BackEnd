<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create Menu
        $menuId = DB::table('menus')->insertGetId([
            'key' => 'returnAssets',
            'name' => 'Return Assets',
            'group' => 'assets',
            'sort_order' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Assign Permissions
        $roles = ['Admin', 'Technician', 'Purchase'];

        foreach ($roles as $roleName) {
            $role = DB::table('roles')->where('name', $roleName)->first();
            
            if ($role) {
                DB::table('role_menu_permissions')->insert([
                    'role_id' => $role->id,
                    'menu_id' => $menuId,
                    'can_view' => true,
                    'can_create' => true,
                    'can_update' => true,
                    'can_delete' => $roleName === 'Admin', // Only Admin can delete? Or maybe Tech/Purchase can't delete history
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $menu = DB::table('menus')->where('key', 'returnAssets')->first();
        
        if ($menu) {
            DB::table('role_menu_permissions')->where('menu_id', $menu->id)->delete();
            DB::table('menus')->where('id', $menu->id)->delete();
        }
    }
};
