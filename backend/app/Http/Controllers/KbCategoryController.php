<?php

namespace App\Http\Controllers;

use App\Models\KbCategory;
use Illuminate\Http\Request;

class KbCategoryController extends Controller
{
    public function index()
    {
        return response()->json(KbCategory::with('children')->whereNull('parent_id')->get());
    }
}
