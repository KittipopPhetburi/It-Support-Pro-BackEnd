<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleManagementTest extends TestCase
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
    public function admin_can_list_roles(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/roles');

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_create_role(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/roles', [
            'name' => 'Custom Role',
            'description' => 'A custom role for testing',
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function admin_can_view_single_role(): void
    {
        $role = Role::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/roles/{$role->id}");

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_update_role(): void
    {
        $role = Role::factory()->create();

        $response = $this->actingAs($this->admin)->putJson("/api/roles/{$role->id}", [
            'name' => 'Updated Role Name',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_delete_role(): void
    {
        $role = Role::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/roles/{$role->id}");

        $response->assertSuccessful();
    }

    #[Test]
    public function unauthenticated_user_cannot_access_roles(): void
    {
        $response = $this->getJson('/api/roles');

        $response->assertStatus(401);
    }
}
