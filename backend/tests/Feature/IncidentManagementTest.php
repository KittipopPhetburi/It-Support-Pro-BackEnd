<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IncidentManagementTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $technician;
    private User $user;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branch = Branch::factory()->create();
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'branch_id' => $this->branch->id,
        ]);
        $this->technician = User::factory()->create([
            'role' => 'technician',
            'branch_id' => $this->branch->id,
        ]);
        $this->user = User::factory()->create([
            'role' => 'user',
            'branch_id' => $this->branch->id,
        ]);
    }

    #[Test]
    public function authenticated_user_can_list_incidents(): void
    {
        Incident::factory()->count(5)->create([
            'branch_id' => $this->branch->id,
            'requester_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/incidents');

        $response->assertStatus(200);
    }

    #[Test]
    public function unauthenticated_user_cannot_list_incidents(): void
    {
        $response = $this->getJson('/api/incidents');

        $response->assertStatus(401);
    }

    #[Test]
    public function user_can_create_incident(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/incidents', [
            'title' => 'Computer not working',
            'description' => 'My computer does not turn on',
            'priority' => 'High',
            'status' => 'Open',
            'category' => 'Hardware',
            'requester_id' => $this->user->id,
            'location' => 'Building A',
            'contact_method' => 'email',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('incidents', [
            'title' => 'Computer not working',
        ]);
    }

    #[Test]
    public function admin_can_view_single_incident(): void
    {
        $incident = Incident::factory()->create([
            'branch_id' => $this->branch->id,
            'requester_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson("/api/incidents/{$incident->id}");

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_update_incident_status(): void
    {
        $incident = Incident::factory()->open()->create([
            'branch_id' => $this->branch->id,
            'requester_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->putJson("/api/incidents/{$incident->id}", [
            'title' => $incident->title,
            'status' => 'In Progress',
            'priority' => 'High',
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_delete_incident(): void
    {
        $incident = Incident::factory()->create([
            'branch_id' => $this->branch->id,
            'requester_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/incidents/{$incident->id}");

        $response->assertSuccessful();
    }
}
