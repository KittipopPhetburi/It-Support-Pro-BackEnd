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
        // Essential Users & Structure
        $this->call(CreateEssentialUsersSeeder::class);
        
        // Operational Data (Incidents, Assets, Requests)
        $this->call(RefreshOperationalDataSeeder::class);
        
        // Base Configuration
        $this->call(IncidentTitleSeeder::class);
        $this->call(BusinessHourSeeder::class);
        $this->call(SystemSettingsSeeder::class);
        $this->call(NotificationSettingsSeeder::class);
        $this->call(SlaSeeder::class);
    }
}
