<?php

namespace App\Http\Controllers;

use App\Models\ProblemStatus;
use Illuminate\Http\Request;

class ProblemStatusController extends Controller
{
    public function index()
    {
        return response()->json(ProblemStatus::orderBy('sort_order')->get());
    }
}
