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
            'reporter_id' => $this->user->id,
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
            'priority' => 'high',
            'category' => 'Hardware',
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
            'reporter_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson("/api/incidents/{$incident->id}");

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_update_incident_status(): void
    {
        $incident = Incident::factory()->open()->create([
            'branch_id' => $this->branch->id,
            'reporter_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->putJson("/api/incidents/{$incident->id}", [
            'status' => 'In Progress',
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_assign_incident_to_technician(): void
    {
        $incident = Incident::factory()->open()->create([
            'branch_id' => $this->branch->id,
            'reporter_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->postJson("/api/incidents/{$incident->id}/assign", [
            'assignee_id' => $this->technician->id,
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function user_can_view_their_own_incidents(): void
    {
        Incident::factory()->count(3)->create([
            'branch_id' => $this->branch->id,
            'reporter_id' => $this->user->id,
        ]);
        Incident::factory()->count(2)->create([
            'branch_id' => $this->branch->id,
            'reporter_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/incidents/my');

        $response->assertStatus(200);
    }

    #[Test]
    public function technician_can_view_assigned_incidents(): void
    {
        Incident::factory()->count(2)->create([
            'branch_id' => $this->branch->id,
            'reporter_id' => $this->user->id,
            'assignee_id' => $this->technician->id,
        ]);

        $response = $this->actingAs($this->technician)->getJson('/api/incidents/assigned');

        $response->assertStatus(200);
    }

    #[Test]
    public function can_filter_incidents_by_status(): void
    {
        Incident::factory()->count(3)->open()->create([
            'branch_id' => $this->branch->id,
            'reporter_id' => $this->user->id,
        ]);
        Incident::factory()->count(2)->resolved()->create([
            'branch_id' => $this->branch->id,
            'reporter_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/incidents?status=Open');

        $response->assertStatus(200);
    }

    #[Test]
    public function can_filter_incidents_by_priority(): void
    {
        Incident::factory()->count(2)->highPriority()->create([
            'branch_id' => $this->branch->id,
            'reporter_id' => $this->user->id,
        ]);
        Incident::factory()->count(3)->critical()->create([
            'branch_id' => $this->branch->id,
            'reporter_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/incidents?priority=critical');

        $response->assertStatus(200);
    }

    #[Test]
    public function can_get_incident_statistics(): void
    {
        Incident::factory()->count(5)->create([
            'branch_id' => $this->branch->id,
            'reporter_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/incidents/statistics');

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_delete_incident(): void
    {
        $incident = Incident::factory()->create([
            'branch_id' => $this->branch->id,
            'reporter_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/incidents/{$incident->id}");

        $response->assertSuccessful();
    }
}
