<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\IncidentCategory;
use App\Models\IncidentPriority;
use App\Models\IncidentStatus;
use Laravel\Sanctum\Sanctum;

class IncidentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_incident()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = IncidentCategory::create(['name' => 'Hardware']);
        $priority = IncidentPriority::create(['name' => 'High', 'level' => 1, 'sla_hours' => 4]);
        $status = IncidentStatus::create(['key' => 'open', 'name' => 'Open']);

        $response = $this->postJson('/api/incidents', [
            'title' => 'Laptop broken',
            'description' => 'Screen is cracked',
            'incident_category_id' => $category->id,
            'priority_id' => $priority->id,
            'status_id' => $status->id,
            'requester_id' => $user->id,
        ]);

        $response->assertStatus(201)
                 ->assertJson(['title' => 'Laptop broken']);

        $this->assertDatabaseHas('incidents', ['title' => 'Laptop broken']);
    }

    public function test_user_can_list_incidents()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/incidents');

        $response->assertStatus(200);
    }
}
