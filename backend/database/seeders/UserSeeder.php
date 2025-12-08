<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'Admin',
            'status' => 'Active',
        ]);
        User::create([
            'name' => 'Technician',
            'username' => 'technician',
            'email' => 'technician@example.com',
            'password' => Hash::make('tech123'),
            'role' => 'Technician',
            'status' => 'Active',
        ]);
        User::create([
            'name' => 'Helpdesk',
            'username' => 'helpdesk',
            'email' => 'helpdesk@example.com',
            'password' => Hash::make('helpdesk123'),
            'role' => 'Helpdesk',
            'status' => 'Active',
        ]);
        User::create([
            'name' => 'Purchase',
            'username' => 'purchase',
            'email' => 'purchase@example.com',
            'password' => Hash::make('purchase123'),
            'role' => 'Purchase',
            'status' => 'Active',
        ]);
        User::create([
            'name' => 'User',
            'username' => 'user',
            'email' => 'user@example.com',
            'password' => Hash::make('user123'),
            'role' => 'User',
            'status' => 'Active',
        ]);
    }
}
