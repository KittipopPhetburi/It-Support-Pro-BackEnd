<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->department(),
            'code' => strtoupper($this->faker->unique()->lexify('DEPT-???')),
            'branch_id' => Branch::factory(),
        ];
    }
}
