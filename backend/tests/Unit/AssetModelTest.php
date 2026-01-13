<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\Branch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AssetModelTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function asset_belongs_to_branch(): void
    {
        $branch = Branch::factory()->create();
        $asset = Asset::factory()->create(['branch_id' => $branch->id]);

        $this->assertInstanceOf(Branch::class, $asset->branch);
        $this->assertEquals($branch->id, $asset->branch->id);
    }

    #[Test]
    public function asset_has_available_status(): void
    {
        $asset = Asset::factory()->available()->create();

        $this->assertEquals('Available', $asset->status);
    }

    #[Test]
    public function asset_has_in_use_status(): void
    {
        $asset = Asset::factory()->inUse()->create();

        $this->assertEquals('In Use', $asset->status);
    }

    #[Test]
    public function asset_has_maintenance_status(): void
    {
        $asset = Asset::factory()->maintenance()->create();

        $this->assertEquals('Maintenance', $asset->status);
    }

    #[Test]
    public function asset_has_required_fields(): void
    {
        $asset = Asset::factory()->create([
            'name' => 'Test Asset',
            'serial_number' => 'SN-12345',
        ]);

        $this->assertEquals('Test Asset', $asset->name);
        $this->assertEquals('SN-12345', $asset->serial_number);
    }

    #[Test]
    public function asset_serial_number_is_unique(): void
    {
        Asset::factory()->create(['serial_number' => 'UNIQUE-SN']);

        $this->assertDatabaseCount('assets', 1);
    }

    #[Test]
    public function asset_can_have_quantity(): void
    {
        $asset = Asset::factory()->create(['quantity' => 5]);

        $this->assertEquals(5, $asset->quantity);
    }
}
