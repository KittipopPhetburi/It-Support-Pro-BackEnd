<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Asset;
use App\Models\User;
use App\Models\Problem;
use App\Models\AssetRequest;
use App\Models\ServiceRequest;
use App\Models\OtherRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * DashboardController - ภาพรวมระบบ (Dashboard)
 * 
 * ไม่ extends BaseCrudController (ไม่มี CRUD)
 * ให้ข้อมูลสรุปสำหรับแสดงบนหน้า Dashboard
 * 
 * Routes (prefix /api/dashboard):
 * - GET /overview              - ภาพรวม: จำนวน incidents, assets, users, problems, requests ที่ pending
 * - GET /incidents-trend       - แนวโน้ม incidents ?days=30
 * - GET /incidents-by-category - แยกตามหมวดหมู่
 * - GET /incidents-by-priority - แยกตามความสำคัญ
 * - GET /top-technicians       - ช่างที่ resolved มากสุด ?limit=10
 * - GET /recent-incidents      - incidents ล่าสุด ?limit=10
 * - GET /sla-compliance        - อัตราการปฏิบัติตาม SLA (met/breached/rate%)
 */
class DashboardController extends Controller
{
    /**
     * Overview - ภาพรวมระบบ
     * 
     * GET /api/dashboard/overview
     * คืนจำนวนรวมของ incidents (ตามสถานะ), assets, users, problems, pending requests
     */
    public function overview()
    {
        return response()->json([
            'incidents' => [
                'total' => Incident::count(),
                'open' => Incident::where('status', 'open')->count(),
                'in_progress' => Incident::where('status', 'in_progress')->count(),
                'resolved' => Incident::where('status', 'resolved')->count(),
                'closed' => Incident::where('status', 'closed')->count(),
            ],
            'assets' => [
                'total' => Asset::count(),
                'available' => Asset::where('status', 'available')->count(),
                'in_use' => Asset::where('status', 'in_use')->count(),
                'maintenance' => Asset::where('status', 'maintenance')->count(),
            ],
            'users' => [
                'total' => User::count(),
                'technicians' => User::where('role', 'technician')->count(),
            ],
            'problems' => [
                'total' => Problem::count(),
                'open' => Problem::where('status', 'open')->count(),
            ],
            'requests' => [
                'asset_requests' => AssetRequest::where('status', 'pending')->count(),
                'service_requests' => ServiceRequest::where('status', 'pending')->count(),
                'other_requests' => OtherRequest::where('status', 'pending')->count(),
            ],
        ]);
    }

    /**
     * Incidents Trend - แนวโน้ม Incidents
     * 
     * GET /api/dashboard/incidents-trend?days=30
     */
    public function incidentsTrend(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $trend = Incident::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'period' => $days . ' days',
            'data' => $trend,
        ]);
    }

    /**
     * Incidents by Category - แยกตามหมวดหมู่
     * 
     * GET /api/dashboard/incidents-by-category
     */
    public function incidentsByCategory()
    {
        $data = Incident::select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderByDesc('count')
            ->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Incidents by Priority - แยกตามความสำคัญ
     * 
     * GET /api/dashboard/incidents-by-priority
     */
    public function incidentsByPriority()
    {
        $data = Incident::select('priority', DB::raw('COUNT(*) as count'))
            ->groupBy('priority')
            ->orderByDesc('count')
            ->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Top Technicians - ช่างยอดเยี่ยม
     * 
     * GET /api/dashboard/top-technicians?limit=10
     */
    public function topTechnicians(Request $request)
    {
        $limit = $request->get('limit', 10);

        $technicians = User::where('role', 'technician')
            ->withCount(['incidentsAssigned as resolved_count' => function ($query) {
                $query->whereIn('status', ['resolved', 'closed']);
            }])
            ->orderByDesc('resolved_count')
            ->limit($limit)
            ->get(['id', 'name', 'email']);

        return response()->json(['data' => $technicians]);
    }

    /**
     * Recent Incidents - Incidents ล่าสุด
     * 
     * GET /api/dashboard/recent-incidents?limit=10
     */
    public function recentIncidents(Request $request)
    {
        $limit = $request->get('limit', 10);

        $incidents = Incident::with(['requester:id,name', 'assignee:id,name'])
            ->latest()
            ->limit($limit)
            ->get();

        return response()->json(['data' => $incidents]);
    }

    /**
     * SLA Compliance - การปฏิบัติตาม SLA
     * 
     * GET /api/dashboard/sla-compliance
     * คืน: total, met, breached, compliance_rate%
     */
    public function slaCompliance()
    {
        $total = Incident::whereNotNull('sla_due_at')->count();
        $met = Incident::whereNotNull('sla_due_at')
            ->whereNotNull('resolved_at')
            ->whereColumn('resolved_at', '<=', 'sla_due_at')
            ->count();
        $breached = Incident::whereNotNull('sla_due_at')
            ->where(function ($query) {
                $query->whereNull('resolved_at')
                    ->where('sla_due_at', '<', now());
            })
            ->orWhere(function ($query) {
                $query->whereNotNull('resolved_at')
                    ->whereColumn('resolved_at', '>', 'sla_due_at');
            })
            ->count();

        return response()->json([
            'total' => $total,
            'met' => $met,
            'breached' => $breached,
            'compliance_rate' => $total > 0 ? round(($met / $total) * 100, 2) : 0,
        ]);
    }
}
