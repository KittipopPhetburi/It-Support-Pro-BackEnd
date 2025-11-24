<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceContract;
use Illuminate\Http\Request;

class MaintenanceContractController extends Controller
{
    public function index()
    {
        return response()->json(MaintenanceContract::with('vendor')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_code' => 'required|unique:maintenance_contracts,contract_code',
            'title' => 'required',
            'vendor_id' => 'required|exists:vendors,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'cost' => 'nullable|numeric',
            'description' => 'nullable',
        ]);

        $contract = MaintenanceContract::create($validated);
        return response()->json($contract, 201);
    }

    public function show(MaintenanceContract $maintenanceContract)
    {
        return response()->json($maintenanceContract->load(['vendor', 'assets']));
    }

    public function update(Request $request, MaintenanceContract $maintenanceContract)
    {
        $validated = $request->validate([
            'contract_code' => 'required|unique:maintenance_contracts,contract_code,' . $maintenanceContract->id,
            'title' => 'required',
            'vendor_id' => 'required|exists:vendors,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'cost' => 'nullable|numeric',
            'description' => 'nullable',
        ]);

        $maintenanceContract->update($validated);
        return response()->json($maintenanceContract);
    }
}
