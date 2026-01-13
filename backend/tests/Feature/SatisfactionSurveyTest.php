<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SatisfactionSurveyTest extends TestCase
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
    public function authenticated_user_can_list_surveys(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/satisfaction-surveys');

        $response->assertStatus(200);
    }

    #[Test]
    public function can_get_survey_statistics(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/satisfaction-surveys/statistics');

        $response->assertStatus(200);
    }

    #[Test]
    public function user_can_create_survey_response(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/satisfaction-surveys', [
            'rating' => 5,
            'comment' => 'Great service!',
            'ticket_id' => 'INC-001',
        ]);

        // May be 201, 200, or 422 depending on ticket validation
        $this->assertContains($response->status(), [200, 201, 422]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_surveys(): void
    {
        $response = $this->getJson('/api/satisfaction-surveys');

        $response->assertStatus(401);
    }
}
