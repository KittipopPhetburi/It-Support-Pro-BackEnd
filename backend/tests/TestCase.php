<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\Role;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create essential roles for all tests if they don't exist
        $this->seedRoles();
    }
    
    /**
     * Seed essential roles for testing
     */
    protected function seedRoles(): void
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Administrator'],
            ['name' => 'manager', 'description' => 'Manager'],
            ['name' => 'technician', 'description' => 'Technician'],
            ['name' => 'helpdesk', 'description' => 'Helpdesk'],
            ['name' => 'user', 'description' => 'Regular User'],
        ];
        
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
