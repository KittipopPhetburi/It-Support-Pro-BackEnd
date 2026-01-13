<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AssetManagementTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branch = Branch::factory()->create();
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'branch_id' => $this->branch->id,
        ]);
    }

    #[Test]
    public function authenticated_user_can_list_assets(): void
    {
        Asset::factory()->count(5)->create(['branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->admin)->getJson('/api/assets');

        $response->assertStatus(200);
    }

    #[Test]
    public function unauthenticated_user_cannot_list_assets(): void
    {
        $response = $this->getJson('/api/assets');

        $response->assertStatus(401);
    }

    #[Test]
    public function admin_can_create_asset(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/assets', [
            'name' => 'Test Laptop',
            'category' => 'Laptop',
            'type' => 'Hardware',
            'brand' => 'Dell',
            'model' => 'Latitude 5520',
            'serial_number' => 'SN-123456',
            'inventory_number' => 'INV-00001',
            'quantity' => 1,
            'status' => 'Available',
            'branch_id' => $this->branch->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('assets', [
            'name' => 'Test Laptop',
            'serial_number' => 'SN-123456',
        ]);
    }

    #[Test]
    public function admin_can_view_single_asset(): void
    {
        $asset = Asset::factory()->create(['branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->admin)->getJson("/api/assets/{$asset->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => $asset->name,
            ]);
    }

    #[Test]
    public function admin_can_update_asset(): void
    {
        $asset = Asset::factory()->create(['branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->admin)->putJson("/api/assets/{$asset->id}", [
            'name' => 'Updated Asset Name',
            'category' => $asset->category,
            'type' => $asset->type,
            'status' => 'In Use',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'name' => 'Updated Asset Name',
        ]);
    }

    #[Test]
    public function admin_can_delete_asset(): void
    {
        $asset = Asset::factory()->create(['branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/assets/{$asset->id}");

        $response->assertSuccessful();
    }

    #[Test]
    public function can_filter_assets_by_status(): void
    {
        Asset::factory()->count(3)->available()->create(['branch_id' => $this->branch->id]);
        Asset::factory()->count(2)->inUse()->create(['branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->admin)->getJson('/api/assets?status=Available');

        $response->assertStatus(200);
    }

    #[Test]
    public function can_filter_assets_by_category(): void
    {
        Asset::factory()->create(['category' => 'Laptop', 'branch_id' => $this->branch->id]);
        Asset::factory()->create(['category' => 'Printer', 'branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->admin)->getJson('/api/assets?category=Laptop');

        $response->assertStatus(200);
    }

    #[Test]
    public function can_search_assets_by_name(): void
    {
        Asset::factory()->create(['name' => 'Dell Laptop', 'branch_id' => $this->branch->id]);
        Asset::factory()->create(['name' => 'HP Printer', 'branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->admin)->getJson('/api/assets?search=Dell');

        $response->assertStatus(200);
    }

    #[Test]
    public function assets_are_filtered_by_user_branch(): void
    {
        $branch2 = Branch::factory()->create();
        Asset::factory()->count(3)->create(['branch_id' => $this->branch->id]);
        Asset::factory()->count(2)->create(['branch_id' => $branch2->id]);

        $user = User::factory()->create([
            'role' => 'user',
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($user)->getJson('/api/assets');

        $response->assertStatus(200);
    }
}
