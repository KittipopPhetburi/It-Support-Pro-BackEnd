<?php

namespace App\Http\Controllers\Api;

use App\Models\PmProject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PmProjectController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = PmProject::class;
    }

    /**
     * Get all PM projects
     */
    public function index(Request $request): JsonResponse
    {
        $query = PmProject::with('projectManager:id,name');

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
                $q->where('project_name', 'like', "%{$search}%")
                    ->orWhere('organization', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate or get all
        if ($request->has('per_page')) {
            $projects = $query->paginate($request->per_page);
        } else {
            $projects = $query->get();
        }

        $data = collect($request->has('per_page') ? $projects->items() : $projects)->map(function ($project) {
            return $this->transformProject($project);
        });

        if ($request->has('per_page')) {
            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'per_page' => $projects->perPage(),
                    'total' => $projects->total(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get a single PM project
     */
    public function show($id): JsonResponse
    {
        $project = PmProject::with('projectManager:id,name')->find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบโครงการที่ระบุ',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformProject($project),
        ]);
    }

    /**
     * Create a new PM project
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'project_value' => 'required|numeric|min:0',
            'project_manager_id' => 'required|exists:users,id',
            'organization' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => ['nullable', Rule::in(['Planning', 'In Progress', 'Completed', 'Cancelled'])],
        ]);

        // Handle file uploads
        if ($request->hasFile('contract_file')) {
            $contractFile = $request->file('contract_file');
            $contractPath = $contractFile->store('pm-projects/contracts', 'public');
            $validated['contract_file_name'] = $contractFile->getClientOriginalName();
            $validated['contract_file_path'] = $contractPath;
        }

        if ($request->hasFile('tor_file')) {
            $torFile = $request->file('tor_file');
            $torPath = $torFile->store('pm-projects/tor', 'public');
            $validated['tor_file_name'] = $torFile->getClientOriginalName();
            $validated['tor_file_path'] = $torPath;
        }

        $project = PmProject::create($validated);
        $project->load('projectManager:id,name');

        return response()->json([
            'success' => true,
            'message' => 'สร้างโครงการสำเร็จ',
            'data' => $this->transformProject($project),
        ], 201);
    }

    /**
     * Update PM project
     */
    public function update(Request $request, $id): JsonResponse
    {
        $project = PmProject::find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบโครงการที่ระบุ',
            ], 404);
        }

        $validated = $request->validate([
            'project_name' => 'sometimes|required|string|max:255',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'project_value' => 'sometimes|required|numeric|min:0',
            'project_manager_id' => 'sometimes|required|exists:users,id',
            'organization' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => ['sometimes', Rule::in(['Planning', 'In Progress', 'Completed', 'Cancelled'])],
        ]);

        // Handle file uploads
        if ($request->hasFile('contract_file')) {
            // Delete old file if exists
            if ($project->contract_file_path) {
                Storage::disk('public')->delete($project->contract_file_path);
            }
            $contractFile = $request->file('contract_file');
            $contractPath = $contractFile->store('pm-projects/contracts', 'public');
            $validated['contract_file_name'] = $contractFile->getClientOriginalName();
            $validated['contract_file_path'] = $contractPath;
        }

        if ($request->hasFile('tor_file')) {
            // Delete old file if exists
            if ($project->tor_file_path) {
                Storage::disk('public')->delete($project->tor_file_path);
            }
            $torFile = $request->file('tor_file');
            $torPath = $torFile->store('pm-projects/tor', 'public');
            $validated['tor_file_name'] = $torFile->getClientOriginalName();
            $validated['tor_file_path'] = $torPath;
        }

        $project->update($validated);
        $project->load('projectManager:id,name');

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตโครงการสำเร็จ',
            'data' => $this->transformProject($project),
        ]);
    }

    /**
     * Delete PM project
     */
    public function destroy($id): JsonResponse
    {
        $project = PmProject::find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบโครงการที่ระบุ',
            ], 404);
        }

        // Delete files if exist
        if ($project->contract_file_path) {
            Storage::disk('public')->delete($project->contract_file_path);
        }
        if ($project->tor_file_path) {
            Storage::disk('public')->delete($project->tor_file_path);
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบโครงการสำเร็จ',
        ]);
    }

    /**
     * Get project statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = PmProject::query();

        if ($request->has('organization')) {
            $query->where('organization', $request->organization);
        }

        $stats = [
            'total' => $query->count(),
            'planning' => (clone $query)->where('status', 'Planning')->count(),
            'in_progress' => (clone $query)->where('status', 'In Progress')->count(),
            'completed' => (clone $query)->where('status', 'Completed')->count(),
            'cancelled' => (clone $query)->where('status', 'Cancelled')->count(),
            'total_value' => (clone $query)->sum('project_value'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Transform project model to API response
     */
    private function transformProject(PmProject $project): array
    {
        return [
            'id' => $project->id,
            'projectName' => $project->project_name,
            'startDate' => $project->start_date->toISOString(),
            'endDate' => $project->end_date->toISOString(),
            'projectValue' => (float) $project->project_value,
            'projectManager' => (string) $project->project_manager_id,
            'projectManagerName' => $project->projectManager?->name ?? 'Unknown',
            'organization' => $project->organization,
            'department' => $project->department,
            'description' => $project->description,
            'contractFileName' => $project->contract_file_name,
            'contractFilePath' => $project->contract_file_path ? Storage::url($project->contract_file_path) : null,
            'torFileName' => $project->tor_file_name,
            'torFilePath' => $project->tor_file_path ? Storage::url($project->tor_file_path) : null,
            'status' => $project->status,
            'createdAt' => $project->created_at->toISOString(),
            'updatedAt' => $project->updated_at->toISOString(),
        ];
    }
}
