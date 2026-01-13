<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles if they don't exist
        Role::firstOrCreate(['name' => 'admin'], ['description' => 'Administrator']);
        Role::firstOrCreate(['name' => 'user'], ['description' => 'Regular User']);
        Role::firstOrCreate(['name' => 'technician'], ['description' => 'Technician']);
        
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    #[Test]
    public function authenticated_user_can_list_users(): void
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/users');

        $response->assertStatus(200);
    }

    #[Test]
    public function unauthenticated_user_cannot_list_users(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }

    #[Test]
    public function admin_can_create_user(): void
    {
        $branch = Branch::factory()->create();

        $response = $this->actingAs($this->admin)->postJson('/api/users', [
            'name' => 'New User',
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => 'user',
            'branch_id' => $branch->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    #[Test]
    public function admin_can_view_single_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'email' => $user->email,
            ]);
    }

    #[Test]
    public function admin_can_update_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->putJson("/api/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'user',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function admin_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/users/{$user->id}");

        $response->assertSuccessful();
    }

    #[Test]
    public function cannot_create_user_with_duplicate_email(): void
    {
        $existingUser = User::factory()->create();

        $response = $this->actingAs($this->admin)->postJson('/api/users', [
            'name' => 'New User',
            'email' => $existingUser->email,
            'password' => 'password123',
            'role' => 'user',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function can_filter_users_by_role(): void
    {
        User::factory()->count(3)->create(['role' => 'user']);
        User::factory()->count(2)->create(['role' => 'technician']);

        $response = $this->actingAs($this->admin)->getJson('/api/users?role=technician');

        $response->assertStatus(200);
    }

    #[Test]
    public function can_search_users_by_name(): void
    {
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $response = $this->actingAs($this->admin)->getJson('/api/users?search=John');

        $response->assertStatus(200);
    }
}
