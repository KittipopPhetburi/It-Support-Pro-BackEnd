<?php

namespace App\Http\Controllers\Api;

use App\Models\KbArticle;
use Illuminate\Http\Request;

class KbArticleController extends BaseCrudController
{
    protected string $modelClass = KbArticle::class;

    protected array $validationRules = [
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'category' => 'nullable|string|max:255',
        'tags' => 'nullable',
        'author' => 'nullable|string|max:255',
        'created_by' => 'nullable|string|max:255',
        'views' => 'nullable|integer|min:0',
        'helpful' => 'nullable|integer|min:0',
        'not_helpful' => 'nullable|integer|min:0',
    ];

    // Override show to increment view count
    public function show($id)
    {
        $article = KbArticle::findOrFail($id);
        $article->increment('views');
        
        return response()->json([
            'success' => true,
            'data' => $article,
        ]);
    }

    // Get popular articles
    public function popular()
    {
        $articles = KbArticle::orderBy('views', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $articles,
        ]);
    }

    // Get recent articles
    public function recent()
    {
        $articles = KbArticle::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $articles,
        ]);
    }

    // Get all categories
    public function categories()
    {
        $categories = KbArticle::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    // Mark as helpful
    public function helpful($id)
    {
        $article = KbArticle::findOrFail($id);
        $article->increment('helpful');

        return response()->json([
            'success' => true,
            'data' => $article->fresh(),
        ]);
    }

    // Mark as not helpful
    public function notHelpful($id)
    {
        $article = KbArticle::findOrFail($id);
        $article->increment('not_helpful');

        return response()->json([
            'success' => true,
            'data' => $article->fresh(),
        ]);
    }
}
