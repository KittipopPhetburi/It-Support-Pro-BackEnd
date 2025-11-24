<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        return response()->json(Vendor::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:vendors,code',
            'name' => 'required',
            'contact_name' => 'nullable',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable',
            'address' => 'nullable',
            'website' => 'nullable|url',
        ]);

        $vendor = Vendor::create($validated);
        return response()->json($vendor, 201);
    }

    public function show(Vendor $vendor)
    {
        return response()->json($vendor->load(['assets', 'maintenanceContracts']));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'code' => 'required|unique:vendors,code,' . $vendor->id,
            'name' => 'required',
            'contact_name' => 'nullable',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable',
            'address' => 'nullable',
            'website' => 'nullable|url',
        ]);

        $vendor->update($validated);
        return response()->json($vendor);
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return response()->json(null, 204);
    }
}
