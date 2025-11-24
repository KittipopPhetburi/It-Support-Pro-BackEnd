<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AssetRequest;
use Illuminate\Http\Request;

class AssetRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_type' => 'required|string',
            'reason' => 'required|string',
            'items' => 'required|array',
            'items.*.asset_category_id' => 'required|exists:asset_categories,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.specification' => 'nullable|string',
        ]);

        $assetRequest = AssetRequest::create([
            'code' => 'AREQ-' . time(),
            'requester_id' => $request->user()->id,
            'request_type' => $validated['request_type'],
            'reason' => $validated['reason'],
            'status_id' => \App\Models\AssetRequestStatus::where('key', 'new')->first()->id ?? 1,
            'requested_date' => now()->toDateString(),
        ]);

        foreach ($validated['items'] as $item) {
            $assetRequest->items()->create($item);
        }

        return response()->json($assetRequest, 201);
    }
}
