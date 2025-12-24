<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PmProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PmProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = PmProject::with('manager');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by organization
        if ($request->has('organization')) {
            $query->where('organization', $request->organization);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('project_code', 'like', "%{$search}%")
                  ->orWhere('organization', 'like', "%{$search}%");
            });
        }

        $projects = $query->orderBy('created_at', 'desc')->get();

        // Calculate stats
        $stats = [
            'total' => PmProject::count(),
            'planning' => PmProject::where('status', 'Planning')->count(),
            'inProgress' => PmProject::where('status', 'In Progress')->count(),
            'completed' => PmProject::where('status', 'Completed')->count(),
            'totalBudget' => PmProject::sum('budget'),
        ];

        return response()->json([
            'data' => $projects,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'organization' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'budget' => 'required|numeric|min:0',
            'manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'status' => 'nullable|in:Planning,In Progress,Completed,Cancelled',
        ]);

        $validated['project_code'] = PmProject::generateProjectCode();
        $validated['status'] = $validated['status'] ?? 'Planning';

        // Handle file uploads
        if ($request->hasFile('contract_file')) {
            $validated['contract_file'] = $request->file('contract_file')->store('pm-projects/contracts', 'public');
        }
        if ($request->hasFile('tor_file')) {
            $validated['tor_file'] = $request->file('tor_file')->store('pm-projects/tor', 'public');
        }

        $project = PmProject::create($validated);

        return response()->json([
            'message' => 'สร้างโครงการสำเร็จ',
            'data' => $project->load('manager'),
        ], 201);
    }

    public function show(PmProject $pmProject)
    {
        return response()->json($pmProject->load('manager'));
    }

    public function update(Request $request, PmProject $pmProject)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'status' => 'nullable|in:Planning,In Progress,Completed,Cancelled',
        ]);

        // Handle file uploads
        if ($request->hasFile('contract_file')) {
            // Delete old file if exists
            if ($pmProject->contract_file) {
                Storage::disk('public')->delete($pmProject->contract_file);
            }
            $validated['contract_file'] = $request->file('contract_file')->store('pm-projects/contracts', 'public');
        }
        if ($request->hasFile('tor_file')) {
            // Delete old file if exists
            if ($pmProject->tor_file) {
                Storage::disk('public')->delete($pmProject->tor_file);
            }
            $validated['tor_file'] = $request->file('tor_file')->store('pm-projects/tor', 'public');
        }

        $pmProject->update($validated);

        return response()->json([
            'message' => 'แก้ไขโครงการสำเร็จ',
            'data' => $pmProject->fresh()->load('manager'),
        ]);
    }

    public function destroy(PmProject $pmProject)
    {
        // Delete associated files
        if ($pmProject->contract_file) {
            Storage::disk('public')->delete($pmProject->contract_file);
        }
        if ($pmProject->tor_file) {
            Storage::disk('public')->delete($pmProject->tor_file);
        }

        $pmProject->delete();

        return response()->json([
            'message' => 'ลบโครงการสำเร็จ',
        ]);
    }
}
