<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use Illuminate\Http\Request;

class KbArticleController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'kb_category_id' => 'required|exists:kb_categories,id',
            'is_published' => 'boolean',
        ]);

        $validated['code'] = 'KB-' . time();
        $validated['created_by_id'] = $request->user()->id;

        $article = KbArticle::create($validated);

        return response()->json($article, 201);
    }

    public function rate(Request $request, KbArticle $kbArticle)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $rating = $kbArticle->ratings()->create([
            'user_id' => $request->user()->id,
            'rating_value' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json($rating);
    }
}
