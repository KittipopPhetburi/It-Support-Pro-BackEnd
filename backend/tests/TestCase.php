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
            ['name' => 'admin', 'display_name' => 'Administrator'],
            ['name' => 'manager', 'display_name' => 'Manager'],
            ['name' => 'technician', 'display_name' => 'Technician'],
            ['name' => 'helpdesk', 'display_name' => 'Helpdesk'],
            ['name' => 'user', 'display_name' => 'Regular User'],
        ];
        
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
