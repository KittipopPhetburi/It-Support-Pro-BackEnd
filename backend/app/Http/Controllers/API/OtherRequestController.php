<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OtherRequest;
use Illuminate\Http\Request;

class OtherRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:other_request_categories,id',
        ]);

        $otherRequest = OtherRequest::create([
            'code' => 'OREQ-' . time(),
            'requester_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'status_id' => \App\Models\OtherRequestStatus::where('key', 'new')->first()->id ?? 1,
            'requested_date' => now()->toDateString(),
        ]);

        return response()->json($otherRequest, 201);
    }
}
