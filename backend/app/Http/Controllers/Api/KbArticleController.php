<?php

namespace App\Http\Controllers\Api;

use App\Models\KbArticle;

class KbArticleController extends BaseCrudController
{
    protected string $modelClass = KbArticle::class;

    protected array $validationRules = [
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'category' => 'nullable|string|max:255',
        'tags' => 'nullable|array',
        'tags.*' => 'string',
        'author_id' => 'required|integer|exists:users,id',
        'created_by_id' => 'nullable|integer|exists:users,id',
        'views' => 'nullable|integer|min:0',
        'helpful' => 'nullable|integer|min:0',
        'not_helpful' => 'nullable|integer|min:0',
    ];
}
