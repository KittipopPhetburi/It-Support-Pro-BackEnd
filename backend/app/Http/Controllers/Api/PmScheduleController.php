<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PmSchedule;
use App\Models\PmChecklistItem;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PmScheduleController extends Controller
{
    /**
     * Display a listing of PM schedules.
     */
    public function index(Request $request): JsonResponse
    {
        // Auto-update overdue schedules
        $this->updateOverdueSchedules();

        $query = PmSchedule::with(['asset', 'assignedTechnician', 'checklistItems']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by assigned technician
        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by asset
        if ($request->has('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('scheduled_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('scheduled_date', '<=', $request->to_date);
        }

        // Filter by frequency
        if ($request->has('frequency')) {
            $query->where('frequency', $request->frequency);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('asset', function ($assetQuery) use ($search) {
                    $assetQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'scheduled_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $schedules = $query->paginate($perPage);

        // Transform data
        $schedules->getCollection()->transform(function ($schedule) {
            return $this->transformSchedule($schedule);
        });

        return response()->json([
            'success' => true,
            'data' => $schedules->items(),
            'meta' => [
                'current_page' => $schedules->currentPage(),
                'last_page' => $schedules->lastPage(),
                'per_page' => $schedules->perPage(),
                'total' => $schedules->total(),
            ],
        ]);
    }

    /**
     * Store a newly created PM schedule.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'frequency' => ['required', Rule::in(['Weekly', 'Monthly', 'Quarterly', 'Semi-Annually', 'Annually'])],
            'assigned_to' => 'required|exists:users,id',
            'scheduled_date' => 'required|date',
            'notes' => 'nullable|string',
            'checklist' => 'nullable|array',
            'checklist.*.title' => 'required|string',
            'checklist.*.description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create PM schedule
            $pmSchedule = PmSchedule::create([
                'asset_id' => $validated['asset_id'],
                'frequency' => $validated['frequency'],
                'assigned_to' => $validated['assigned_to'],
                'scheduled_date' => $validated['scheduled_date'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'Scheduled',
            ]);

            // Calculate next scheduled date
            $pmSchedule->next_scheduled_date = $pmSchedule->calculateNextScheduledDate();
            $pmSchedule->save();

            // Create checklist items
            if (isset($validated['checklist']) && count($validated['checklist']) > 0) {
                foreach ($validated['checklist'] as $index => $item) {
                    PmChecklistItem::create([
                        'pm_schedule_id' => $pmSchedule->id,
                        'title' => $item['title'],
                        'description' => $item['description'] ?? null,
                        'sort_order' => $index,
                    ]);
                }
            } else {
                // Create default checklist based on asset type
                $this->createDefaultChecklist($pmSchedule);
            }

            DB::commit();

            $pmSchedule->load(['asset', 'assignedTechnician', 'checklistItems']);

            return response()->json([
                'success' => true,
                'message' => 'สร้างแผน PM สำเร็จ',
                'data' => $this->transformSchedule($pmSchedule),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการสร้างแผน PM',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified PM schedule.
     */
    public function show(PmSchedule $pmSchedule): JsonResponse
    {
        $pmSchedule->load(['asset', 'assignedTechnician', 'completedByUser', 'checklistItems']);

        return response()->json([
            'success' => true,
            'data' => $this->transformSchedule($pmSchedule),
        ]);
    }

    /**
     * Update the specified PM schedule.
     */
    public function update(Request $request, PmSchedule $pmSchedule): JsonResponse
    {
        $validated = $request->validate([
            'asset_id' => 'sometimes|exists:assets,id',
            'frequency' => ['sometimes', Rule::in(['Weekly', 'Monthly', 'Quarterly', 'Semi-Annually', 'Annually'])],
            'assigned_to' => 'sometimes|exists:users,id',
            'scheduled_date' => 'sometimes|date',
            'status' => ['sometimes', Rule::in(['Scheduled', 'In Progress', 'Completed', 'Overdue', 'Cancelled'])],
            'check_result' => ['nullable', Rule::in(['Pass', 'Fail', 'NeedsRepair'])],
            'notes' => 'nullable|string',
            'issues_found' => 'nullable|array',
            'recommendations' => 'nullable|string',
            'images' => 'nullable|array',
            'checklist' => 'nullable|array',
            'checklist.*.id' => 'nullable|integer',
            'checklist.*.title' => 'required|string',
            'checklist.*.description' => 'nullable|string',
            'checklist.*.is_completed' => 'boolean',
            'checklist.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Update PM schedule
            $pmSchedule->update($validated);

            // Recalculate next scheduled date if frequency or scheduled_date changed
            if (isset($validated['frequency']) || isset($validated['scheduled_date'])) {
                $pmSchedule->next_scheduled_date = $pmSchedule->calculateNextScheduledDate();
                $pmSchedule->save();
            }

            // If status changed to Completed
            if (isset($validated['status']) && $validated['status'] === 'Completed') {
                $pmSchedule->completed_at = now();
                $pmSchedule->completed_by = auth()->id();
                $pmSchedule->save();
            }

            // Update checklist items if provided
            if (isset($validated['checklist'])) {
                foreach ($validated['checklist'] as $index => $item) {
                    if (isset($item['id'])) {
                        // Update existing item
                        $checklistItem = PmChecklistItem::find($item['id']);
                        if ($checklistItem && $checklistItem->pm_schedule_id === $pmSchedule->id) {
                            $checklistItem->update([
                                'title' => $item['title'],
                                'description' => $item['description'] ?? null,
                                'is_completed' => $item['is_completed'] ?? false,
                                'notes' => $item['notes'] ?? null,
                                'completed_at' => ($item['is_completed'] ?? false) ? now() : null,
                                'sort_order' => $index,
                            ]);
                        }
                    } else {
                        // Create new item
                        PmChecklistItem::create([
                            'pm_schedule_id' => $pmSchedule->id,
                            'title' => $item['title'],
                            'description' => $item['description'] ?? null,
                            'is_completed' => $item['is_completed'] ?? false,
                            'notes' => $item['notes'] ?? null,
                            'completed_at' => ($item['is_completed'] ?? false) ? now() : null,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            DB::commit();

            $pmSchedule->load(['asset', 'assignedTechnician', 'completedByUser', 'checklistItems']);

            return response()->json([
                'success' => true,
                'message' => 'อัปเดตแผน PM สำเร็จ',
                'data' => $this->transformSchedule($pmSchedule),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการอัปเดตแผน PM',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified PM schedule.
     */
    public function destroy(PmSchedule $pmSchedule): JsonResponse
    {
        try {
            $pmSchedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบแผน PM สำเร็จ',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการลบแผน PM',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute PM - update checklist and complete.
     */
    public function execute(Request $request, PmSchedule $pmSchedule): JsonResponse
    {
        $validated = $request->validate([
            'checklist' => 'required|array',
            'checklist.*.id' => 'required|integer|exists:pm_checklist_items,id',
            'checklist.*.is_completed' => 'required|boolean',
            'checklist.*.notes' => 'nullable|string',
            'check_result' => ['nullable', Rule::in(['Pass', 'Fail', 'NeedsRepair'])],
            'issues_found' => 'nullable|array',
            'recommendations' => 'nullable|string',
            'images' => 'nullable|array',
            'complete' => 'boolean', // Whether to mark as completed
        ]);

        DB::beginTransaction();
        try {
            // Update checklist items
            foreach ($validated['checklist'] as $item) {
                $checklistItem = PmChecklistItem::find($item['id']);
                if ($checklistItem && $checklistItem->pm_schedule_id === $pmSchedule->id) {
                    $checklistItem->update([
                        'is_completed' => $item['is_completed'],
                        'notes' => $item['notes'] ?? null,
                        'completed_at' => $item['is_completed'] ? now() : null,
                    ]);
                }
            }

            // Update PM schedule
            $updateData = [
                'check_result' => $validated['check_result'] ?? null,
                'issues_found' => $validated['issues_found'] ?? null,
                'recommendations' => $validated['recommendations'] ?? null,
                'images' => $validated['images'] ?? null,
            ];

            // Check if all items are completed
            $allCompleted = $pmSchedule->checklistItems()->where('is_completed', false)->count() === 0;

            if (($validated['complete'] ?? false) && $allCompleted) {
                $updateData['status'] = 'Completed';
                $updateData['completed_at'] = now();
                $updateData['completed_by'] = auth()->id();
            } else {
                $updateData['status'] = 'In Progress';
            }

            $pmSchedule->update($updateData);

            DB::commit();

            $pmSchedule->load(['asset', 'assignedTechnician', 'completedByUser', 'checklistItems']);

            return response()->json([
                'success' => true,
                'message' => $updateData['status'] === 'Completed' ? 'บันทึกและปิดงาน PM สำเร็จ' : 'บันทึกความคืบหน้าสำเร็จ',
                'data' => $this->transformSchedule($pmSchedule),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการบันทึกงาน PM',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get PM statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = PmSchedule::query();

        // Filter by date range
        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('scheduled_date', $request->month + 1) // JS months are 0-indexed
                ->whereYear('scheduled_date', $request->year);
        }

        $total = $query->count();
        $scheduled = (clone $query)->where('status', 'Scheduled')->count();
        $inProgress = (clone $query)->where('status', 'In Progress')->count();
        $completed = (clone $query)->where('status', 'Completed')->count();
        $overdue = (clone $query)->where('status', 'Overdue')->count();
        $cancelled = (clone $query)->where('status', 'Cancelled')->count();

        // Check results statistics
        $checkResults = [
            'pass' => (clone $query)->where('check_result', 'Pass')->count(),
            'fail' => (clone $query)->where('check_result', 'Fail')->count(),
            'needsRepair' => (clone $query)->where('check_result', 'NeedsRepair')->count(),
        ];

        // PM by asset type
        $byAssetType = Asset::select('type')
            ->selectRaw('COUNT(pm_schedules.id) as total')
            ->selectRaw('SUM(CASE WHEN pm_schedules.status = "Completed" THEN 1 ELSE 0 END) as completed')
            ->join('pm_schedules', 'assets.id', '=', 'pm_schedules.asset_id')
            ->groupBy('type')
            ->get();

        // Upcoming PMs (next 7 days)
        $upcoming = PmSchedule::with(['asset', 'assignedTechnician'])
            ->upcoming(7)
            ->limit(5)
            ->get()
            ->map(fn($s) => $this->transformSchedule($s));

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'scheduled' => $scheduled,
                'inProgress' => $inProgress,
                'completed' => $completed,
                'overdue' => $overdue,
                'cancelled' => $cancelled,
                'successRate' => $total > 0 ? round(($completed / $total) * 100) : 0,
                'checkResults' => $checkResults,
                'byAssetType' => $byAssetType,
                'upcoming' => $upcoming,
            ],
        ]);
    }

    /**
     * Transform PM schedule for API response.
     */
    private function transformSchedule(PmSchedule $schedule): array
    {
        return [
            'id' => 'PM-' . str_pad($schedule->id, 3, '0', STR_PAD_LEFT),
            'dbId' => $schedule->id,
            'assetId' => (string) $schedule->asset_id,
            'assetName' => $schedule->asset->name ?? '',
            'assetType' => $schedule->asset->type ?? '',
            'organization' => $schedule->asset->organization ?? '',
            'department' => $schedule->asset->department ?? '',
            'frequency' => $schedule->frequency,
            'assignedTo' => (string) $schedule->assigned_to,
            'assignedToName' => $schedule->assignedTechnician->name ?? '',
            'scheduledDate' => $schedule->scheduled_date?->toISOString(),
            'nextScheduledDate' => $schedule->next_scheduled_date?->toISOString(),
            'status' => $schedule->status,
            'checkResult' => $schedule->check_result,
            'notes' => $schedule->notes,
            'issuesFound' => $schedule->issues_found ?? [],
            'recommendations' => $schedule->recommendations,
            'images' => $schedule->images ?? [],
            'completedAt' => $schedule->completed_at?->toISOString(),
            'completedBy' => $schedule->completed_by ? (string) $schedule->completed_by : null,
            'completedByName' => $schedule->completedByUser->name ?? null,
            'createdAt' => $schedule->created_at?->toISOString(),
            'updatedAt' => $schedule->updated_at?->toISOString(),
            'checklist' => $schedule->checklistItems->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'isCompleted' => $item->is_completed,
                    'notes' => $item->notes,
                    'completedAt' => $item->completed_at?->toISOString(),
                ];
            })->toArray(),
        ];
    }

    /**
     * Create default checklist based on asset type.
     */
    private function createDefaultChecklist(PmSchedule $pmSchedule): void
    {
        $asset = Asset::find($pmSchedule->asset_id);
        $assetType = $asset->type ?? 'General';

        $checklists = [
            'Computer' => [
                ['title' => 'ทำความสะอาดภายในเครื่อง', 'description' => 'เปิดฝาเครื่องและทำความสะอาดฝุ่น'],
                ['title' => 'ตรวจสอบฮาร์ดแวร์', 'description' => 'ตรวจสอบ RAM, HDD/SSD, พัดลม'],
                ['title' => 'อัปเดตซอฟต์แวร์', 'description' => 'อัปเดต Windows และโปรแกรมต่างๆ'],
                ['title' => 'ตรวจสอบความปลอดภัย', 'description' => 'สแกนไวรัสและตรวจสอบ Firewall'],
            ],
            'Printer' => [
                ['title' => 'ตรวจสอบความสะอาดเครื่อง', 'description' => null],
                ['title' => 'ตรวจสอบการทำงานของหัวพิมพ์', 'description' => null],
                ['title' => 'ทำความสะอาดลูกกลิ้ง', 'description' => null],
                ['title' => 'ตรวจสอบระดับหมึก/โทนเนอร์', 'description' => null],
            ],
            'Scanner' => [
                ['title' => 'ทำความสะอาดกระจกสแกน', 'description' => null],
                ['title' => 'ตรวจสอบการทำงานของเซ็นเซอร์', 'description' => null],
                ['title' => 'ทดสอบคุณภาพการสแกน', 'description' => null],
            ],
        ];

        $defaultChecklist = $checklists[$assetType] ?? [
            ['title' => 'ตรวจสอบสภาพทั่วไป', 'description' => null],
            ['title' => 'ทำความสะอาด', 'description' => null],
            ['title' => 'ทดสอบการทำงาน', 'description' => null],
        ];

        foreach ($defaultChecklist as $index => $item) {
            PmChecklistItem::create([
                'pm_schedule_id' => $pmSchedule->id,
                'title' => $item['title'],
                'description' => $item['description'],
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * Update overdue PM schedules.
     * Schedules that are past their scheduled date and still in 'Scheduled' or 'In Progress' status
     * will be marked as 'Overdue'.
     */
    private function updateOverdueSchedules(): void
    {
        PmSchedule::whereIn('status', ['Scheduled', 'In Progress'])
            ->whereDate('scheduled_date', '<', now()->toDateString())
            ->update(['status' => 'Overdue']);
    }
}
