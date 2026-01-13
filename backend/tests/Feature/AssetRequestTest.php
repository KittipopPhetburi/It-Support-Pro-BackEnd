<?php

namespace Tests\Feature;

use App\Models\AssetRequest;
use App\Models\Asset;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AssetRequestTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
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
        $this->user = User::factory()->create([
            'role' => 'user',
            'branch_id' => $this->branch->id,
        ]);
    }

    #[Test]
    public function authenticated_user_can_list_asset_requests(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/asset-requests');

        $response->assertStatus(200);
    }

    #[Test]
    public function unauthenticated_user_cannot_list_asset_requests(): void
    {
        $response = $this->getJson('/api/asset-requests');

        $response->assertStatus(401);
    }

    #[Test]
    public function can_filter_asset_requests_by_status(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/asset-requests?status=Pending');

        $response->assertStatus(200);
    }

    #[Test]
    public function user_can_view_their_own_requests(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/asset-requests/my');

        $response->assertStatus(200);
    }
}
