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
            'asset_id' => Asset::factory(),
            'purpose' => $this->faker->sentence(),
            'request_type' => $this->faker->randomElement(['borrow', 'purchase', 'transfer']),
            'quantity' => 1,
            'status' => 'pending',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }
}
