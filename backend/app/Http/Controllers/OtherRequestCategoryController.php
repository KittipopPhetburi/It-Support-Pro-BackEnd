<?php

namespace App\Http\Controllers;

use App\Models\OtherRequestCategory;
use Illuminate\Http\Request;

class OtherRequestCategoryController extends Controller
{
    public function index()
    {
        return response()->json(OtherRequestCategory::all());
    }
}
