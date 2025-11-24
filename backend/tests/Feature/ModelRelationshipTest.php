<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Role;
use App\Models\Incident;
use App\Models\Service;
use App\Models\Asset;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_models_can_be_instantiated_and_relationships_called(): void
    {
        // Just checking if we can instantiate and call relationship methods without error.
        // We are not asserting database content here, just code structure validity.

        $user = new User();
        $this->assertTrue(method_exists($user, 'role'));
        $this->assertTrue(method_exists($user, 'branch'));
        $this->assertTrue(method_exists($user, 'department'));
        $this->assertTrue(method_exists($user, 'incidentsRequested'));

        $branch = new Branch();
        $this->assertTrue(method_exists($branch, 'departments'));
        $this->assertTrue(method_exists($branch, 'users'));

        $incident = new Incident();
        $this->assertTrue(method_exists($incident, 'service'));
        $this->assertTrue(method_exists($incident, 'requester'));

        $service = new Service();
        $this->assertTrue(method_exists($service, 'category'));

        $asset = new Asset();
        $this->assertTrue(method_exists($asset, 'category'));
        $this->assertTrue(method_exists($asset, 'vendor'));
    }
}
