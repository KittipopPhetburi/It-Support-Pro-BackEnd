<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetStatus;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;

class AssetApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_assign_assets()
    {
        // Setup Data
        $user = User::factory()->create();
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);
        $status = AssetStatus::create(['name' => 'Available', 'key' => 'available']);
        $vendor = Vendor::create(['name' => 'Dell', 'code' => 'DELL']);

        // Create Asset
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/assets', [
            'code' => 'AST-001',
            'name' => 'Dell XPS 15',
            'asset_category_id' => $category->id,
            'asset_status_id' => $status->id,
            'vendor_id' => $vendor->id,
        ]);

        $response->assertStatus(201);
        $assetId = $response->json('id');

        // Verify Created
        $this->assertDatabaseHas('assets', ['id' => $assetId, 'code' => 'AST-001']);

        // Assign Asset
        $response = $this->postJson("/api/assets/{$assetId}/assign", [
            'user_id' => $user->id,
            'start_date' => now()->toDateString(),
            'note' => 'Assigned for work',
        ]);

        $response->assertStatus(201);

        // Verify Assignment
        $this->assertDatabaseHas('asset_assignments', [
            'asset_id' => $assetId,
            'user_id' => $user->id,
            'end_date' => null,
        ]);

        // Unassign Asset
        $response = $this->postJson("/api/assets/{$assetId}/unassign", [
            'end_date' => now()->toDateString(),
            'note' => 'Returned',
        ]);

        $response->assertStatus(200);

        // Verify Unassignment
        $this->assertDatabaseMissing('asset_assignments', [
            'asset_id' => $assetId,
            'user_id' => $user->id,
            'end_date' => null,
        ]);
    }
}
