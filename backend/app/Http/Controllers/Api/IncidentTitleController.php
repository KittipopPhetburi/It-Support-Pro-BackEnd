<?php

namespace App\Http\Controllers\Api;

use App\Models\IncidentTitle;
use Illuminate\Http\Request;

class IncidentTitleController extends BaseCrudController
{
    protected string $modelClass = IncidentTitle::class;

    protected array $validationRules = [
        'title' => 'required|string|max:255',
        'category' => 'required|string|max:100',
        'priority' => 'required|string|in:Critical,High,Medium,Low',
        'response_time' => 'required|integer|min:1',
        'resolution_time' => 'required|integer|min:1',
        'is_active' => 'boolean',
    ];

    /**
     * Get all active incident titles
     */
    public function all()
    {
        return response()->json([
            'data' => IncidentTitle::where('is_active', true)
                ->orderBy('category')
                ->orderBy('title')
                ->get(),
        ]);
    }

    /**
     * Get all categories
     */
    public function categories()
    {
        $categories = IncidentTitle::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Get titles by category
     */
    public function byCategory($category)
    {
        $titles = IncidentTitle::where('category', $category)
            ->where('is_active', true)
            ->orderBy('title')
            ->get();

        return response()->json([
            'data' => $titles,
        ]);
    }

    /**
     * Toggle active status
     */
    public function toggle($id)
    {
        $title = IncidentTitle::findOrFail($id);
        $title->is_active = !$title->is_active;
        $title->save();

        return response()->json([
            'message' => 'Status updated successfully',
            'data' => $title,
        ]);
    }
}
