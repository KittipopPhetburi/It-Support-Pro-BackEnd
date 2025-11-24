<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return response()->json(Service::with(['category', 'defaultPriority'])->get());
    }

    public function show(Service $service)
    {
        return response()->json($service->load(['category', 'defaultPriority']));
    }
}
