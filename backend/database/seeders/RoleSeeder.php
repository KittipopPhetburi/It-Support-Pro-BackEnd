<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'Admin', 'display_name' => 'Admin (ผู้ดูแลระบบ)'],
            ['name' => 'Technician', 'display_name' => 'Technician (ช่างซ่อม)'],
            ['name' => 'Helpdesk', 'display_name' => 'Helpdesk (รับแจ้งงาน)'],
            ['name' => 'Purchase', 'display_name' => 'Purchase (จัดซื้อ)'],
            ['name' => 'User', 'display_name' => 'User (ผู้ใช้งานทั่วไป)'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']], 
                ['display_name' => $role['display_name']]
            );
        }

        $this->command?->info('Default roles seeded successfully.');
    }
}
