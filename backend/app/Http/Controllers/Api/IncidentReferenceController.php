<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IncidentReferenceController extends Controller
{
    /**
     * Get all incident categories
     */
    public function getCategories()
    {
        $categories = [
            ['id' => 'Hardware', 'name' => 'Hardware', 'label' => 'Hardware'],
            ['id' => 'Software', 'name' => 'Software', 'label' => 'Software'],
            ['id' => 'Network', 'name' => 'Network', 'label' => 'Network'],
            ['id' => 'Account', 'name' => 'Account', 'label' => 'Account'],
            ['id' => 'Email', 'name' => 'Email', 'label' => 'Email'],
            ['id' => 'Security', 'name' => 'Security', 'label' => 'Security'],
            ['id' => 'Other', 'name' => 'Other', 'label' => 'Other'],
        ];

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get all incident priorities
     */
    public function getPriorities()
    {
        $priorities = [
            ['id' => 'Low', 'name' => 'Low', 'label' => 'Low', 'color' => 'bg-green-100 text-green-800'],
            ['id' => 'Medium', 'name' => 'Medium', 'label' => 'Medium', 'color' => 'bg-yellow-100 text-yellow-800'],
            ['id' => 'High', 'name' => 'High', 'label' => 'High', 'color' => 'bg-orange-100 text-orange-800'],
            ['id' => 'Critical', 'name' => 'Critical', 'label' => 'Critical', 'color' => 'bg-red-100 text-red-800'],
        ];

        return response()->json([
            'success' => true,
            'data' => $priorities
        ]);
    }

    /**
     * Get all incident statuses
     */
    public function getStatuses()
    {
        $statuses = [
            ['id' => 'Open', 'name' => 'Open', 'label' => 'Open', 'color' => 'bg-blue-100 text-blue-800'],
            ['id' => 'In Progress', 'name' => 'In Progress', 'label' => 'In Progress', 'color' => 'bg-purple-100 text-purple-800'],
            ['id' => 'Pending', 'name' => 'Pending', 'label' => 'Pending', 'color' => 'bg-gray-100 text-gray-800'],
            ['id' => 'Resolved', 'name' => 'Resolved', 'label' => 'Resolved', 'color' => 'bg-green-100 text-green-800'],
            ['id' => 'Closed', 'name' => 'Closed', 'label' => 'Closed', 'color' => 'bg-slate-100 text-slate-800'],
        ];

        return response()->json([
            'success' => true,
            'data' => $statuses
        ]);
    }
}
