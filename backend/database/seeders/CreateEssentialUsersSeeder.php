<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Organization;

class CreateEssentialUsersSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure minimal reference data exists (Branch/Department)
        // Check/Create Branch
        $branch = Branch::first();
        if (!$branch) {
            $branch = Branch::create([
                'name' => 'Main Branch',
                'code' => 'HQ',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('Created default Branch: Main Branch');
        }

        // Check/Create Department
        $dept = Department::first();
        if (!$dept) {
            $dept = Department::create([
                'name' => 'IT Department',
                'code' => 'IT',
                'branch_id' => $branch->id,
                'status' => 'Active',
            ]);
            $this->command->info('Created default Department: IT Department');
        }

        // 2. Define Users to create
        $users = [
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'email' => 'admin@itsupport.com',
                'password' => 'password', // Default password
                'role' => 'Admin',
                'branch_id' => $branch->id,
                'department_id' => $dept->id,
                'organization' => 'IT Support Pro',
                'status' => 'Active',
            ],
            [
                'name' => 'Purchase Manager',
                'username' => 'purchase',
                'email' => 'purchase@itsupport.com',
                'password' => 'password',
                'role' => 'Purchase',
                'branch_id' => $branch->id,
                'department_id' => $dept->id,
                'organization' => 'IT Support Pro',
                'status' => 'Active',
            ],
            [
                'name' => 'Helpdesk Support',
                'username' => 'helpdesk',
                'email' => 'helpdesk@itsupport.com',
                'password' => 'password',
                'role' => 'Helpdesk',
                'branch_id' => $branch->id,
                'department_id' => $dept->id,
                'organization' => 'IT Support Pro',
                'status' => 'Active',
            ],
            [
                'name' => 'Technician One',
                'username' => 'tech',
                'email' => 'tech@itsupport.com',
                'password' => 'password',
                'role' => 'Technician',
                'branch_id' => $branch->id,
                'department_id' => $dept->id,
                'organization' => 'IT Support Pro',
                'status' => 'Active',
            ],
            [
                'name' => 'General User',
                'username' => 'user',
                'email' => 'user@itsupport.com',
                'password' => 'password',
                'role' => 'User',
                'branch_id' => $branch->id,
                'department_id' => $dept->id,
                'organization' => 'IT Support Pro',
                'status' => 'Active',
            ],
        ];

        foreach ($users as $userData) {
            // Check if email already exists
            if (User::where('email', $userData['email'])->exists()) {
                $this->command->info("User {$userData['email']} already exists. Skipping.");
                continue;
            }

            User::create([
                'name' => $userData['name'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'role' => $userData['role'],
                'branch_id' => $userData['branch_id'],
                'department_id' => $userData['department_id'],
                'organization' => $userData['organization'],
                'status' => $userData['status'],
                'email_verified_at' => now(),
            ]);
            
            $this->command?->info("Created user: {$userData['name']} ({$userData['role']})");
        }
        
        $this->command?->info('Essential users seeding completed!');
        $this->command?->info('Default password for all users is: password');
    }
}
