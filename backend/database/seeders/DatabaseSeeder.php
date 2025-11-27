<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create([
            'key' => 'admin',
            'name' => 'Admin',
            'description' => 'System Administrator',
        ]);

        $userRole = Role::create([
            'key' => 'user',
            'name' => 'User',
            'description' => 'Regular User',
        ]);

        $technicianRole = Role::create([
            'key' => 'technician',
            'name' => 'Technician',
            'description' => 'IT Technician',
        ]);

        $purchaseRole = Role::create([
            'key' => 'purchase',
            'name' => 'Purchase',
            'description' => 'Purchase Department',
        ]);

        $helpdeskRole = Role::create([
            'key' => 'helpdesk',
            'name' => 'Helpdesk',
            'description' => 'Helpdesk Support',
        ]);

        // Create users for each role (password: user123)
        User::factory()->create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'admin123',
            'role_id' => $adminRole->id,
        ]);

        User::factory()->create([
            'name' => 'Regular User',
            'username' => 'user',
            'email' => 'user@example.com',
            'password' => 'user123',
            'role_id' => $userRole->id,
        ]);

        User::factory()->create([
            'name' => 'Technician User',
            'username' => 'technician',
            'email' => 'technician@example.com',
            'password' => 'tech123',
            'role_id' => $technicianRole->id,
        ]);

        User::factory()->create([
            'name' => 'Purchase User',
            'username' => 'purchase',
            'email' => 'purchase@example.com',
            'password' => 'purchase123',
            'role_id' => $purchaseRole->id,
        ]);

        User::factory()->create([
            'name' => 'Helpdesk User',
            'username' => 'helpdesk',
            'email' => 'helpdesk@example.com',
            'password' => 'helpdesk123',
            'role_id' => $helpdeskRole->id,
        ]);
    }
}
