<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    #[Test]
    public function health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'database',
                'services',
            ]);
    }

    #[Test]
    public function health_endpoint_shows_api_running(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'ok',
            ]);
    }

    #[Test]
    public function database_health_endpoint_works(): void
    {
        $response = $this->getJson('/api/health/database');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'connected',
                'responseTime',
                'timestamp',
            ]);
    }

    #[Test]
    public function login_endpoint_exists(): void
    {
        $response = $this->postJson('/api/login', []);

        // Should return 422 (validation error) not 404
        $this->assertNotEquals(404, $response->status());
    }

    #[Test]
    public function register_endpoint_exists(): void
    {
        $response = $this->postJson('/api/register', []);

        // Should return 422 (validation error) not 404
        $this->assertNotEquals(404, $response->status());
    }

    #[Test]
    public function protected_routes_require_authentication(): void
    {
        $response = $this->getJson('/api/me');
        $response->assertStatus(401);

        $response = $this->getJson('/api/assets');
        $response->assertStatus(401);

        $response = $this->getJson('/api/incidents');
        $response->assertStatus(401);

        $response = $this->getJson('/api/dashboard/overview');
        $response->assertStatus(401);
    }
}
