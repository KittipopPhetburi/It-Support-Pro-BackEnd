<?php

namespace App\Http\Controllers;

use App\Models\KbArticle;
use App\Models\KbArticleRating;
use Illuminate\Http\Request;

class KbArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = KbArticle::query();

        if ($request->has('kb_category_id')) {
            $query->where('kb_category_id', $request->kb_category_id);
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        // Only published articles usually, but let's assume all for now or check status if exists
        // Schema check: 'is_published' boolean?
        // Let's assume standard CRUD for now.

        return response()->json($query->with('category')->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required',
            'content' => 'required',
            'kb_category_id' => 'required|exists:kb_categories,id',
            'is_published' => 'boolean',
        ]);

        $validated['code'] = 'KB-' . time();
        $validated['created_by_id'] = $request->user()->id ?? 1;

        $article = KbArticle::create($validated);
        return response()->json($article, 201);
    }

    public function show(KbArticle $kbArticle)
    {
        // Increment view count if exists?
        // $kbArticle->increment('view_count');
        
        return response()->json($kbArticle->load(['category', 'attachments', 'ratings']));
    }

    public function update(Request $request, KbArticle $kbArticle)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required',
            'content' => 'sometimes|required',
            'kb_category_id' => 'sometimes|exists:kb_categories,id',
            'is_published' => 'boolean',
        ]);

        $validated['updated_by_id'] = $request->user()->id ?? 1;

        $kbArticle->update($validated);
        return response()->json($kbArticle);
    }

    public function destroy(KbArticle $kbArticle)
    {
        $kbArticle->delete();
        return response()->json(null, 204);
    }

    public function rate(Request $request, KbArticle $kbArticle)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $rating = $kbArticle->ratings()->updateOrCreate(
            ['user_id' => $request->user()->id ?? 1],
            ['rating_value' => $validated['rating'], 'comment' => $validated['comment'] ?? null]
        );

        return response()->json($rating);
    }
}
