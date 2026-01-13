<?php

namespace Database\Factories;

use App\Models\AssetRequest;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetRequestFactory extends Factory
{
    protected $model = AssetRequest::class;

    public function definition(): array
    {
        return [
            'requester_id' => User::factory(),
            'asset_type' => $this->faker->randomElement(['Laptop', 'Desktop', 'Monitor', 'Printer']),
            'quantity' => 1,
            'status' => 'Pending',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Pending',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Approved',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Rejected',
        ]);
    }
}
