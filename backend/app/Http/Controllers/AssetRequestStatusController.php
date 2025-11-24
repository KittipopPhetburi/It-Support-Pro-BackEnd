<?php

namespace App\Http\Controllers;

use App\Models\AssetRequestStatus;
use Illuminate\Http\Request;

class AssetRequestStatusController extends Controller
{
    public function index()
    {
        return response()->json(AssetRequestStatus::orderBy('sort_order')->get());
    }
}
