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

        // Seed branches and departments
        $this->call(BranchSeeder::class);
        $this->call(DepartmentSeeder::class);

        // Seed assets
        $this->call(AssetSeeder::class);

        // Seed SLA configurations
        $this->call(SlaSeeder::class);

        // Seed system settings
        $this->call(SystemSettingsSeeder::class);

        // Seed organization notifications
        $this->call(OrganizationNotificationsSeeder::class);

        // Seed incident titles
        $this->call(IncidentTitleSeeder::class);

        // Seed incident references
        $this->call(IncidentReferenceSeeder::class);

        // Seed asset requests
        $this->call(AssetRequestSeeder::class);
    }
}
