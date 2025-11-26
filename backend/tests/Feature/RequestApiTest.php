<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AssetCategory;
use App\Models\AssetRequestStatus;
use App\Models\OtherRequestCategory;
use App\Models\OtherRequestStatus;
use Laravel\Sanctum\Sanctum;

class RequestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_asset_request()
    {
        $this->withoutExceptionHandling();

        // Setup Data
        $user = User::factory()->create();
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);
        $status = AssetRequestStatus::create(['name' => 'New', 'key' => 'new']);

        // Create Asset Request
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/asset-requests', [
            'request_type' => 'new',
            'reason' => 'Need new laptop',
            'items' => [
                [
                    'asset_category_id' => $category->id,
                    'quantity' => 1,
                    'specification' => 'High performance',
                ]
            ]
        ]);

        $response->assertStatus(201);
        $requestId = $response->json('id');

        // Verify Created
        $this->assertDatabaseHas('asset_requests', ['id' => $requestId, 'request_type' => 'new']);
        $this->assertDatabaseHas('asset_request_items', ['asset_request_id' => $requestId, 'asset_category_id' => $category->id]);
    }

    public function test_can_create_other_request()
    {
        $this->withoutExceptionHandling();

        // Setup Data
        $user = User::factory()->create();
        $category = OtherRequestCategory::create(['name' => 'General']);
        $status = OtherRequestStatus::create(['name' => 'New', 'key' => 'new']);

        // Create Other Request
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/other-requests', [
            'title' => 'Office Cleaning',
            'description' => 'Please clean the office',
            'category_id' => $category->id,
        ]);

        $response->assertStatus(201);
        $requestId = $response->json('id');

        // Verify Created
        $this->assertDatabaseHas('other_requests', ['id' => $requestId, 'title' => 'Office Cleaning']);
    }
}
