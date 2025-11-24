<?php

namespace App\Http\Controllers;

use App\Models\OtherRequestStatus;
use Illuminate\Http\Request;

class OtherRequestStatusController extends Controller
{
    public function index()
    {
        return response()->json(OtherRequestStatus::orderBy('sort_order')->get());
    }
}
