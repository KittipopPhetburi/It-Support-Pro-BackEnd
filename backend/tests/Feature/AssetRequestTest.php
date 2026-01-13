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
    public function user_can_create_asset_request(): void
    {
        $asset = Asset::factory()->available()->create(['branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->user)->postJson('/api/asset-requests', [
            'asset_id' => $asset->id,
            'purpose' => 'Need for project work',
            'request_type' => 'borrow',
            'quantity' => 1,
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function admin_can_approve_asset_request(): void
    {
        $asset = Asset::factory()->available()->create(['branch_id' => $this->branch->id]);
        $assetRequest = AssetRequest::factory()->create([
            'requester_id' => $this->user->id,
            'asset_id' => $asset->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->postJson("/api/asset-requests/{$assetRequest->id}/approve");

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_reject_asset_request(): void
    {
        $asset = Asset::factory()->available()->create(['branch_id' => $this->branch->id]);
        $assetRequest = AssetRequest::factory()->create([
            'requester_id' => $this->user->id,
            'asset_id' => $asset->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->postJson("/api/asset-requests/{$assetRequest->id}/reject", [
            'rejection_reason' => 'Asset not available',
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function can_view_single_asset_request(): void
    {
        $asset = Asset::factory()->create(['branch_id' => $this->branch->id]);
        $assetRequest = AssetRequest::factory()->create([
            'requester_id' => $this->user->id,
            'asset_id' => $asset->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson("/api/asset-requests/{$assetRequest->id}");

        $response->assertStatus(200);
    }

    #[Test]
    public function can_filter_asset_requests_by_status(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/asset-requests?status=pending');

        $response->assertStatus(200);
    }

    #[Test]
    public function user_can_view_their_own_requests(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/asset-requests/my');

        $response->assertStatus(200);
    }
}
