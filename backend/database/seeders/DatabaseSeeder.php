<?php

namespace Database\Seeders;

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
        // Seed users
        $this->call(UserSeeder::class);

        // Seed roles, menus, and admin permissions
        $this->call(RoleMenuPermissionSeeder::class);

        // Incident references (categories, priorities, statuses)
        $this->call(IncidentReferenceSeeder::class);

        // Incident titles
        $this->call(IncidentTitleSeeder::class);

        // System settings
        $this->call(SystemSettingsSeeder::class);

        // Organization notifications
        $this->call(OrganizationNotificationsSeeder::class);
    }
}
