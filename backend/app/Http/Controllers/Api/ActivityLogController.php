<?php

namespace App\Http\Controllers\Api;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends BaseCrudController
{
    protected string $modelClass = ActivityLog::class;

    protected array $validationRules = [
        'user_id' => 'nullable|integer|exists:users,id',
        'user_role' => 'nullable|string|max:50',
        'user_email' => 'nullable|email|max:255',
        'action' => 'required|string|max:255',
        'severity' => 'nullable|in:INFO,WARN,ERROR,CRITICAL',
        'event_type' => 'nullable|string|max:50',
        'module' => 'required|string|max:255',
        'timestamp' => 'nullable|date',
        'details' => 'nullable|string',
        'ip_address' => 'nullable|string|max:45',
        'user_agent' => 'nullable|string',
        'session_id' => 'nullable|string|max:255',
        'device_type' => 'nullable|string|max:50',
        'browser' => 'nullable|string|max:100',
        'os' => 'nullable|string|max:100',
        'target_type' => 'nullable|string|max:100',
        'target_id' => 'nullable|string|max:100',
        'target_name' => 'nullable|string|max:255',
        'old_value' => 'nullable|string',
        'new_value' => 'nullable|string',
        'request_method' => 'nullable|string|max:10',
        'request_url' => 'nullable|string|max:500',
        'response_status' => 'nullable|integer',
        'response_time' => 'nullable|integer',
    ];

    /**
     * Override index เพื่อ include user และเรียงตาม timestamp ล่าสุด
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user:id,name,email')
            ->orderBy('timestamp', 'desc');

        // Filter by module
        if ($request->has('module') && $request->module !== 'all') {
            $query->where('module', $request->module);
        }

        // Filter by user
        if ($request->has('userId')) {
            $query->where('user_id', $request->userId);
        }

        // Filter by action
        if ($request->has('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }

        // Filter by severity
        if ($request->has('severity') && $request->severity !== 'all') {
            $query->where('severity', $request->severity);
        }

        // Filter by event_type
        if ($request->has('event_type') && $request->event_type !== 'all') {
            $query->where('event_type', $request->event_type);
        }

        // Filter by date range
        if ($request->has('startDate')) {
            $query->whereDate('timestamp', '>=', $request->startDate);
        }
        if ($request->has('endDate')) {
            $query->whereDate('timestamp', '<=', $request->endDate);
        }

        // Filter for security logs only
        if ($request->has('security') && $request->security === 'true') {
            $query->securityLogs();
        }

        // Filter for error logs only
        if ($request->has('errors') && $request->errors === 'true') {
            $query->errorLogs();
        }

        // รองรับ pagination
        $perPage = $request->get('limit', $request->get('per_page', 100));
        
        return $query->paginate($perPage);
    }

    /**
     * Override store เพื่อเพิ่มข้อมูลอัตโนมัติ
     */
    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules);
        
        // ดึง IP address จาก request
        $data['ip_address'] = $data['ip_address'] ?? $request->ip();
        
        // ดึง User Agent จาก request header
        $data['user_agent'] = $data['user_agent'] ?? $request->header('User-Agent');
        
        // Parse user agent for device info
        if (!empty($data['user_agent']) && empty($data['browser'])) {
            $parsed = $this->parseUserAgent($data['user_agent']);
            $data['device_type'] = $data['device_type'] ?? $parsed['device_type'];
            $data['browser'] = $data['browser'] ?? $parsed['browser'];
            $data['os'] = $data['os'] ?? $parsed['os'];
        }
        
        // ถ้าไม่ได้ส่ง user_id มา ให้ใช้ user ที่ login อยู่
        if (!isset($data['user_id']) && Auth::check()) {
            $data['user_id'] = Auth::id();
            $data['user_role'] = $data['user_role'] ?? Auth::user()->role ?? null;
            $data['user_email'] = $data['user_email'] ?? Auth::user()->email ?? null;
        }

        // ถ้าไม่ได้ส่ง timestamp มา ให้ใช้เวลาปัจจุบัน
        if (!isset($data['timestamp'])) {
            $data['timestamp'] = now();
        }

        // ถ้าไม่ได้ส่ง severity มา ให้กำหนดตาม action
        if (!isset($data['severity'])) {
            $data['severity'] = $this->getSeverityByAction($data['action']);
        }

        // ถ้าไม่ได้ส่ง event_type มา ให้กำหนดตาม action
        if (!isset($data['event_type'])) {
            $data['event_type'] = $this->getEventTypeByAction($data['action']);
        }

        $model = ActivityLog::create($data);

        return response()->json($model, 201);
    }

    /**
     * Get statistics for dashboard
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('startDate', now()->subDays(7));
        $endDate = $request->get('endDate', now());

        $stats = [
            'total_logs' => ActivityLog::whereBetween('timestamp', [$startDate, $endDate])->count(),
            'by_severity' => ActivityLog::whereBetween('timestamp', [$startDate, $endDate])
                ->selectRaw('severity, COUNT(*) as count')
                ->groupBy('severity')
                ->pluck('count', 'severity'),
            'by_action' => ActivityLog::whereBetween('timestamp', [$startDate, $endDate])
                ->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('count', 'action'),
            'by_module' => ActivityLog::whereBetween('timestamp', [$startDate, $endDate])
                ->selectRaw('module, COUNT(*) as count')
                ->groupBy('module')
                ->orderByDesc('count')
                ->pluck('count', 'module'),
            'by_user' => ActivityLog::whereBetween('timestamp', [$startDate, $endDate])
                ->whereNotNull('user_id')
                ->selectRaw('user_id, COUNT(*) as count')
                ->groupBy('user_id')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'security_events' => ActivityLog::whereBetween('timestamp', [$startDate, $endDate])
                ->securityLogs()
                ->count(),
            'error_events' => ActivityLog::whereBetween('timestamp', [$startDate, $endDate])
                ->errorLogs()
                ->count(),
            'login_attempts' => [
                'success' => ActivityLog::whereBetween('timestamp', [$startDate, $endDate])
                    ->where('action', 'LOGIN')
                    ->count(),
                'failed' => ActivityLog::whereBetween('timestamp', [$startDate, $endDate])
                    ->where('action', 'LOGIN_FAILED')
                    ->count(),
            ],
        ];

        return response()->json($stats);
    }

    /**
     * Get security logs
     */
    public function securityLogs(Request $request)
    {
        $query = ActivityLog::with('user:id,name,email')
            ->securityLogs()
            ->orderBy('timestamp', 'desc');

        $perPage = $request->get('limit', 50);
        
        return $query->paginate($perPage);
    }

    /**
     * Get error logs
     */
    public function errorLogs(Request $request)
    {
        $query = ActivityLog::with('user:id,name,email')
            ->errorLogs()
            ->orderBy('timestamp', 'desc');

        $perPage = $request->get('limit', 50);
        
        return $query->paginate($perPage);
    }

    /**
     * Parse user agent string
     */
    private function parseUserAgent($userAgent)
    {
        $result = [
            'device_type' => 'desktop',
            'browser' => 'Unknown',
            'os' => 'Unknown',
        ];

        // Detect device type
        if (preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent)) {
            if (preg_match('/iPad/i', $userAgent)) {
                $result['device_type'] = 'tablet';
            } else {
                $result['device_type'] = 'mobile';
            }
        }

        // Detect browser
        if (preg_match('/Firefox\//i', $userAgent)) {
            $result['browser'] = 'Firefox';
        } elseif (preg_match('/Edg\//i', $userAgent)) {
            $result['browser'] = 'Edge';
        } elseif (preg_match('/Chrome\//i', $userAgent)) {
            $result['browser'] = 'Chrome';
        } elseif (preg_match('/Safari\//i', $userAgent)) {
            $result['browser'] = 'Safari';
        } elseif (preg_match('/MSIE|Trident/i', $userAgent)) {
            $result['browser'] = 'IE';
        }

        // Detect OS
        if (preg_match('/Windows NT 10/i', $userAgent)) {
            $result['os'] = 'Windows 10/11';
        } elseif (preg_match('/Windows NT 6.3/i', $userAgent)) {
            $result['os'] = 'Windows 8.1';
        } elseif (preg_match('/Windows NT 6.2/i', $userAgent)) {
            $result['os'] = 'Windows 8';
        } elseif (preg_match('/Windows NT 6.1/i', $userAgent)) {
            $result['os'] = 'Windows 7';
        } elseif (preg_match('/Mac OS X/i', $userAgent)) {
            $result['os'] = 'macOS';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $result['os'] = 'Android';
        } elseif (preg_match('/iOS|iPhone|iPad/i', $userAgent)) {
            $result['os'] = 'iOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $result['os'] = 'Linux';
        }

        return $result;
    }

    /**
     * Get severity level by action type
     */
    private function getSeverityByAction($action)
    {
        $severityMap = [
            'LOGIN_FAILED' => 'WARN',
            'ACCESS_DENIED' => 'WARN',
            'DELETE' => 'WARN',
            'ERROR' => 'ERROR',
            'CRITICAL' => 'CRITICAL',
        ];

        return $severityMap[strtoupper($action)] ?? 'INFO';
    }

    /**
     * Get event type by action
     */
    private function getEventTypeByAction($action)
    {
        $eventTypeMap = [
            'LOGIN' => 'AUTH',
            'LOGOUT' => 'AUTH',
            'LOGIN_FAILED' => 'SECURITY',
            'ACCESS_DENIED' => 'SECURITY',
            'VIEW' => 'ACCESS',
            'NAVIGATE' => 'ACCESS',
            'CREATE' => 'CHANGE',
            'UPDATE' => 'CHANGE',
            'DELETE' => 'CHANGE',
            'STATUS_CHANGE' => 'CHANGE',
            'ASSIGN' => 'CHANGE',
            'APPROVE' => 'CHANGE',
            'REJECT' => 'CHANGE',
            'EXPORT' => 'ACCESS',
            'PRINT' => 'ACCESS',
            'SEARCH' => 'ACCESS',
            'SUBMIT' => 'CHANGE',
        ];

        return $eventTypeMap[strtoupper($action)] ?? 'SYSTEM';
    }
}
