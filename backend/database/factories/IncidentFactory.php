<?php

namespace Database\Factories;

use App\Models\Incident;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    public function definition(): array
    {
        $priorities = ['Low', 'Medium', 'High', 'Critical'];
        $statuses = ['Open', 'In Progress', 'Resolved', 'Closed'];
        $categories = ['Hardware', 'Software', 'Network', 'Security', 'Other'];

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'priority' => $this->faker->randomElement($priorities),
            'status' => $this->faker->randomElement($statuses),
            'category' => $this->faker->randomElement($categories),
            'requester_id' => User::factory(),
            'branch_id' => Branch::factory(),
            'location' => $this->faker->city(),
            'contact_method' => $this->faker->randomElement(['phone', 'email', 'walk-in']),
            'contact_phone' => $this->faker->phoneNumber(),
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Open',
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'In Progress',
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Resolved',
            'resolved_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Closed',
            'resolved_at' => now()->subDay(),
            'closed_at' => now(),
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'High',
        ]);
    }

    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'Critical',
        ]);
    }
}
