<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KnowledgeBaseTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $branch = Branch::factory()->create();
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'branch_id' => $branch->id,
        ]);
    }

    #[Test]
    public function authenticated_user_can_list_kb_articles(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/kb-articles');

        $response->assertStatus(200);
    }

    #[Test]
    public function can_get_popular_articles(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/kb-articles/popular');

        $response->assertStatus(200);
    }

    #[Test]
    public function can_get_recent_articles(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/kb-articles/recent');

        $response->assertStatus(200);
    }

    #[Test]
    public function can_get_article_categories(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/kb-articles/categories');

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_create_kb_article(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/kb-articles', [
            'title' => 'How to reset password',
            'content' => 'Step 1: Go to login page...',
            'category' => 'Account',
            'status' => 'published',
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_kb(): void
    {
        $response = $this->getJson('/api/kb-articles');

        $response->assertStatus(401);
    }
}
