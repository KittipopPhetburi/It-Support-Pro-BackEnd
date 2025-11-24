<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Problem;
use App\Models\ProblemStatus;
use App\Models\Incident;
use App\Models\Service;
use App\Models\IncidentCategory;
use App\Models\IncidentPriority;
use App\Models\IncidentStatus;
use Laravel\Sanctum\Sanctum;

class ProblemApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_manage_problems()
    {
        // Setup Data
        $user = User::factory()->create();
        $status = ProblemStatus::create(['key' => 'open', 'name' => 'Open', 'sort_order' => 1]);
        
        // Create Problem
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/problems', [
            'title' => 'Test Problem',
            'description' => 'Test Description',
            'status_id' => $status->id,
            'owner_id' => $user->id,
        ]);

        $response->assertStatus(201);
        $problemId = $response->json('id');

        // Verify Created
        $this->assertDatabaseHas('problems', ['id' => $problemId, 'title' => 'Test Problem']);

        // Setup Incident
        $serviceCategory = \App\Models\ServiceCategory::create(['name' => 'Cat 1', 'description' => 'Desc']);
        $service = Service::create([
            'service_category_id' => $serviceCategory->id,
            'code' => 'S1', 
            'name' => 'Service 1',
            'description' => 'Desc',
            'is_active' => true,
        ]);
        $incCategory = IncidentCategory::create(['name' => 'Cat 1']);
        $priority = IncidentPriority::create(['name' => 'High', 'level' => 1, 'sla_hours' => 4]);
        $incStatus = IncidentStatus::create(['key' => 'open', 'name' => 'Open', 'sort_order' => 1]);

        $incident = Incident::create([
            'code' => 'INC-001',
            'title' => 'Test Incident',
            'description' => 'Test Description',
            'service_id' => $service->id,
            'incident_category_id' => $incCategory->id,
            'priority_id' => $priority->id,
            'status_id' => $incStatus->id,
            'requester_id' => $user->id,
            'source' => 'web',
        ]);

        // Attach Incident
        $response = $this->postJson("/api/problems/{$problemId}/incidents", [
            'incident_id' => $incident->id,
        ]);
        $response->assertStatus(200);

        // Verify Attachment
        $this->assertDatabaseHas('problem_incidents', [
            'problem_id' => $problemId,
            'incident_id' => $incident->id,
        ]);

        // Detach Incident
        $response = $this->deleteJson("/api/problems/{$problemId}/incidents", [
            'incident_id' => $incident->id,
        ]);
        $response->assertStatus(200);

        // Verify Detachment
        $this->assertDatabaseMissing('problem_incidents', [
            'problem_id' => $problemId,
            'incident_id' => $incident->id,
        ]);
    }
}
