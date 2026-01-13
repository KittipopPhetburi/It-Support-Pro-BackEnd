<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $branch = Branch::factory()->create();
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'branch_id' => $branch->id,
        ]);
    }

    #[Test]
    public function authenticated_user_can_get_dashboard_overview(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/dashboard/overview');

        $response->assertStatus(200);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->getJson('/api/dashboard/overview');

        $response->assertStatus(401);
    }

    #[Test]
    public function can_get_recent_incidents(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/dashboard/recent-incidents');

        $response->assertStatus(200);
    }
}
