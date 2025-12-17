<?php

namespace Database\Seeders;

use App\Models\BusinessHour;
use Illuminate\Database\Seeder;

class BusinessHourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        BusinessHour::truncate();

        // Define business hours for each day of the week
        // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
        $businessHours = [
            [
                'day_of_week' => 0, // Sunday
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_working_day' => false,
            ],
            [
                'day_of_week' => 1, // Monday
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_working_day' => false,
            ],
            [
                'day_of_week' => 2, // Tuesday
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_working_day' => true,
            ],
            [
                'day_of_week' => 3, // Wednesday
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_working_day' => true,
            ],
            [
                'day_of_week' => 4, // Thursday
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_working_day' => true,
            ],
            [
                'day_of_week' => 5, // Friday
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_working_day' => true,
            ],
            [
                'day_of_week' => 6, // Saturday
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_working_day' => false,
            ],
        ];

        foreach ($businessHours as $hour) {
            BusinessHour::create($hour);
        }
    }
}
