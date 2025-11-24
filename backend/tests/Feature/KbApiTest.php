<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\KbCategory;
use App\Models\KbArticle;
use Laravel\Sanctum\Sanctum;

class KbApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_rate_articles()
    {
        // Setup Data
        $user = User::factory()->create();
        $category = KbCategory::create(['name' => 'General', 'description' => 'General Info']);

        // Create Article
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/kb-articles', [
            'title' => 'How to reset password',
            'content' => 'Go to settings...',
            'kb_category_id' => $category->id,
            'is_published' => true,
        ]);

        $response->assertStatus(201);
        $articleId = $response->json('id');

        // Verify Created
        $this->assertDatabaseHas('kb_articles', ['id' => $articleId, 'title' => 'How to reset password']);

        // Rate Article
        $response = $this->postJson("/api/kb-articles/{$articleId}/rate", [
            'rating' => 5,
            'comment' => 'Very helpful',
        ]);

        $response->assertStatus(200);

        // Verify Rating
        $this->assertDatabaseHas('kb_article_ratings', [
            'kb_article_id' => $articleId,
            'user_id' => $user->id,
            'rating_value' => 5,
            'comment' => 'Very helpful',
        ]);
    }
}
