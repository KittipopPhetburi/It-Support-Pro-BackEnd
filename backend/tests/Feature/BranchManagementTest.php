<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BranchManagementTest extends TestCase
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
    public function admin_can_list_branches(): void
    {
        Branch::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/branches');

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_create_branch(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/branches', [
            'name' => 'New Branch',
            'code' => 'BR001',
            'address' => '123 Test Street',
            'phone' => '1234567890',
            'email' => 'branch@example.com',
            'is_active' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('branches', [
            'name' => 'New Branch',
        ]);
    }

    #[Test]
    public function admin_can_view_single_branch(): void
    {
        $branch = Branch::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/branches/{$branch->id}");

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_update_branch(): void
    {
        $branch = Branch::factory()->create();

        $response = $this->actingAs($this->admin)->putJson("/api/branches/{$branch->id}", [
            'name' => 'Updated Branch Name',
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_delete_branch(): void
    {
        $branch = Branch::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/branches/{$branch->id}");

        $response->assertSuccessful();
    }

    #[Test]
    public function unauthenticated_user_cannot_access_branches(): void
    {
        $response = $this->getJson('/api/branches');

        $response->assertStatus(401);
    }
}
