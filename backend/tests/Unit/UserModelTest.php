<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function user_belongs_to_branch(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);

        $this->assertInstanceOf(Branch::class, $user->branch);
    }

    #[Test]
    public function user_has_default_role(): void
    {
        $user = User::factory()->create();

        $this->assertEquals('user', $user->role);
    }

    #[Test]
    public function user_can_be_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertEquals('admin', $user->role);
    }

    #[Test]
    public function user_can_be_technician(): void
    {
        $user = User::factory()->create(['role' => 'technician']);

        $this->assertEquals('technician', $user->role);
    }

    #[Test]
    public function user_has_email_verified_at(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->email_verified_at);
    }

    #[Test]
    public function user_can_be_unverified(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);
    }

    #[Test]
    public function user_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $this->assertNotEquals('password', $user->password);
    }

    #[Test]
    public function user_email_is_unique(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->assertDatabaseCount('users', 1);
    }
}
