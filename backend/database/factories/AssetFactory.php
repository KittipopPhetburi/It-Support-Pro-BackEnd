<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        $categories = ['Computer', 'Laptop', 'Printer', 'Monitor', 'Software', 'Network'];
        $statuses = ['Available', 'In Use', 'Maintenance', 'Retired'];

        return [
            'name' => $this->faker->words(3, true),
            'category' => $this->faker->randomElement($categories),
            'type' => $this->faker->randomElement(['Hardware', 'Software']),
            'brand' => $this->faker->company(),
            'model' => $this->faker->bothify('Model-##??'),
            'serial_number' => $this->faker->unique()->uuid(),
            'inventory_number' => $this->faker->unique()->numerify('INV-#####'),
            'quantity' => 1,
            'status' => $this->faker->randomElement($statuses),
            'location' => $this->faker->city(),
            'purchase_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'warranty_expiry' => $this->faker->dateTimeBetween('now', '+3 years'),
            'branch_id' => Branch::factory(),
        ];
    }

    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Available',
        ]);
    }

    public function inUse(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'In Use',
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Maintenance',
        ]);
    }
}
