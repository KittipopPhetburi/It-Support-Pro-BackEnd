<?php

namespace Tests\Unit;

use App\Models\Incident;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IncidentModelTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function incident_belongs_to_reporter(): void
    {
        $user = User::factory()->create();
        $incident = Incident::factory()->create(['reporter_id' => $user->id]);

        $this->assertInstanceOf(User::class, $incident->reporter);
        $this->assertEquals($user->id, $incident->reporter->id);
    }

    #[Test]
    public function incident_belongs_to_branch(): void
    {
        $branch = Branch::factory()->create();
        $incident = Incident::factory()->create(['branch_id' => $branch->id]);

        $this->assertInstanceOf(Branch::class, $incident->branch);
    }

    #[Test]
    public function incident_can_have_assignee(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);
        $incident = Incident::factory()->create(['assignee_id' => $technician->id]);

        $this->assertInstanceOf(User::class, $incident->assignee);
    }

    #[Test]
    public function incident_has_open_status(): void
    {
        $incident = Incident::factory()->open()->create();

        $this->assertEquals('Open', $incident->status);
    }

    #[Test]
    public function incident_has_in_progress_status(): void
    {
        $incident = Incident::factory()->inProgress()->create();

        $this->assertEquals('In Progress', $incident->status);
    }

    #[Test]
    public function incident_has_resolved_status(): void
    {
        $incident = Incident::factory()->resolved()->create();

        $this->assertEquals('Resolved', $incident->status);
        $this->assertNotNull($incident->resolved_at);
    }

    #[Test]
    public function incident_has_closed_status(): void
    {
        $incident = Incident::factory()->closed()->create();

        $this->assertEquals('Closed', $incident->status);
        $this->assertNotNull($incident->closed_at);
    }

    #[Test]
    public function incident_has_priority_levels(): void
    {
        $highPriority = Incident::factory()->highPriority()->create();
        $critical = Incident::factory()->critical()->create();

        $this->assertEquals('high', $highPriority->priority);
        $this->assertEquals('critical', $critical->priority);
    }

    #[Test]
    public function incident_has_required_fields(): void
    {
        $incident = Incident::factory()->create([
            'title' => 'Test Incident',
            'description' => 'Test Description',
        ]);

        $this->assertEquals('Test Incident', $incident->title);
        $this->assertEquals('Test Description', $incident->description);
    }
}
