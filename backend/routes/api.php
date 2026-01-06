<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\{
    AuthController,
    BranchController,
    DepartmentController,
    UserController,
    AssetController,
    IncidentController,
    IncidentTitleController,
    ProblemController,
    AssetRequestController,
    OtherRequestController,
    ServiceCatalogItemController,
    ServiceRequestController,
    ActivityLogController,
    SatisfactionSurveyController,
    BusinessHourController,
    HolidayController,
    KbArticleController,
    SubContractorController,
    SystemSettingController,
    OrganizationNotificationController,
    SlaController,
    SlaCalculatorController,
    NotificationController,
    DashboardController,
    RolePermissionController,
    RoleController,
    UserPermissionController,
    PmScheduleController,
    PmProjectController
};


/*
|--------------------------------------------------------------------------
| Public Routes - ไม่ต้อง Login
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes - ต้อง Login
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/password', [AuthController::class, 'updatePassword']);
    
    // Users
    Route::get('/users/technicians', [UserController::class, 'technicians']);
    Route::apiResource('users', UserController::class);
    Route::get('/technicians', [UserController::class, 'getTechnicians']);

    // Branches
    Route::get('/branches/all', [BranchController::class, 'all']);
    Route::apiResource('branches', BranchController::class);

    // Departments
    Route::get('/departments/all', [DepartmentController::class, 'all']);
    Route::apiResource('departments', DepartmentController::class);

    // Assets
    Route::get('/assets/statistics', [AssetController::class, 'statistics']);
    Route::get('/assets/{asset}/maintenance-history', [AssetController::class, 'maintenanceHistory']);
    Route::post('/assets/bulk', [AssetController::class, 'bulkStore']);
    Route::post('/assets/check-serial-numbers', [AssetController::class, 'checkSerialNumbers']);
    Route::apiResource('assets', AssetController::class);

    // Incidents
    Route::get('/incidents/statistics', [IncidentController::class, 'statistics']);
    Route::get('/incidents/my', [IncidentController::class, 'myIncidents']);
    Route::get('/incidents/assigned', [IncidentController::class, 'assignedIncidents']);
    Route::post('/incidents/{incident}/assign', [IncidentController::class, 'assign']);
    Route::apiResource('incidents', IncidentController::class);

    // Incident Titles (for BusinessHours management)
    Route::get('/incident-titles/all', [IncidentTitleController::class, 'all']);
    Route::get('/incident-titles/categories', [IncidentTitleController::class, 'categories']);
    Route::get('/incident-titles/category/{category}', [IncidentTitleController::class, 'byCategory']);
    Route::post('/incident-titles/{id}/toggle', [IncidentTitleController::class, 'toggle']);
    Route::apiResource('incident-titles', IncidentTitleController::class);

    // Problems
    Route::get('/problems/statistics', [ProblemController::class, 'statistics']);
    Route::post('/problems/{problem}/link-incidents', [ProblemController::class, 'linkIncidents']);
    Route::post('/problems/{problem}/unlink-incidents', [ProblemController::class, 'unlinkIncidents']);
    Route::apiResource('problems', ProblemController::class);

    // Asset Requests
    Route::get('/asset-requests/statistics', [AssetRequestController::class, 'statistics']);
    Route::get('/asset-requests/my', [AssetRequestController::class, 'myRequests']);
    Route::post('/asset-requests/{assetRequest}/approve', [AssetRequestController::class, 'approve']);
    Route::post('/asset-requests/{assetRequest}/reject', [AssetRequestController::class, 'reject']);
    Route::apiResource('asset-requests', AssetRequestController::class);

    // Other Requests
    Route::get('/other-requests/statistics', [OtherRequestController::class, 'statistics']);
    Route::get('/other-requests/my', [OtherRequestController::class, 'myRequests']);
    Route::post('/other-requests/{id}/approve', [OtherRequestController::class, 'approve']);
    Route::post('/other-requests/{id}/reject', [OtherRequestController::class, 'reject']);
    Route::post('/other-requests/{id}/complete', [OtherRequestController::class, 'complete']);
    Route::post('/other-requests/{id}/receive', [OtherRequestController::class, 'receive']);
    Route::apiResource('other-requests', OtherRequestController::class);

    // Service Catalog
    Route::get('/service-catalog/all', [ServiceCatalogItemController::class, 'all']);
    Route::get('/service-catalog/categories', [ServiceCatalogItemController::class, 'categories']);
    Route::apiResource('service-catalog', ServiceCatalogItemController::class);

    // Service Requests
    Route::get('/service-requests/statistics', [ServiceRequestController::class, 'statistics']);
    Route::get('/service-requests/my', [ServiceRequestController::class, 'myRequests']);
    Route::post('/service-requests/{serviceRequest}/approve', [ServiceRequestController::class, 'approve']);
    Route::post('/service-requests/{serviceRequest}/reject', [ServiceRequestController::class, 'reject']);
    Route::post('/service-requests/{serviceRequest}/start-progress', [ServiceRequestController::class, 'startProgress']);
    Route::post('/service-requests/{serviceRequest}/complete', [ServiceRequestController::class, 'complete']);
    Route::apiResource('service-requests', ServiceRequestController::class);

    // SLAs
    Route::get('/slas/all', [SlaController::class, 'all']);
    Route::get('/slas/priority/{priority}', [SlaController::class, 'getByPriority']);
    Route::apiResource('slas', SlaController::class);

    // SLA Calculator (Business Hours based)
    Route::get('/sla-calculator/incident/{incidentId}', [SlaCalculatorController::class, 'calculateForIncident']);
    Route::post('/sla-calculator/calculate', [SlaCalculatorController::class, 'calculate']);
    Route::post('/sla-calculator/business-minutes', [SlaCalculatorController::class, 'calculateBusinessMinutes']);
    Route::post('/sla-calculator/deadline', [SlaCalculatorController::class, 'getDeadline']);
    Route::get('/sla-calculator/is-business-hours', [SlaCalculatorController::class, 'isWithinBusinessHours']);
    Route::get('/sla-calculator/open-incidents', [SlaCalculatorController::class, 'getOpenIncidentsSlaStatus']);

    // Knowledge Base
    Route::get('/kb-articles/popular', [KbArticleController::class, 'popular']);
    Route::get('/kb-articles/recent', [KbArticleController::class, 'recent']);
    Route::get('/kb-articles/categories', [KbArticleController::class, 'categories']);
    Route::post('/kb-articles/{id}/helpful', [KbArticleController::class, 'helpful']);
    Route::post('/kb-articles/{id}/not-helpful', [KbArticleController::class, 'notHelpful']);
    Route::apiResource('kb-articles', KbArticleController::class);

    // Activity Logs
    Route::get('/activity-logs/my', [ActivityLogController::class, 'myLogs']);
    Route::get('/activity-logs/actions', [ActivityLogController::class, 'actions']);
    Route::get('/activity-logs/modules', [ActivityLogController::class, 'modules']);
    Route::get('/activity-logs/statistics', [ActivityLogController::class, 'statistics']);
    Route::get('/activity-logs/security', [ActivityLogController::class, 'securityLogs']);
    Route::get('/activity-logs/errors', [ActivityLogController::class, 'errorLogs']);
    Route::delete('/activity-logs/clear-old', [ActivityLogController::class, 'clearOldLogs']);
    Route::apiResource('activity-logs', ActivityLogController::class);

    // Notifications
    Route::get('/notifications/my', [NotificationController::class, 'myNotifications']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll']);
    Route::apiResource('notifications', NotificationController::class);

    // Role management (CRUD)
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{role}', [RoleController::class, 'update']);
    Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
    
    // Role permissions
    Route::get('/roles/{role}/permissions', [RolePermissionController::class, 'index']);
    Route::put('/roles/{role}/permissions', [RolePermissionController::class, 'update']);
    Route::post('/roles/{role}/permissions/reset-default', [RolePermissionController::class, 'resetToDefault']);

    // User permissions (individual override)
    Route::get('/users/{user}/permissions', [UserPermissionController::class, 'index']);
    Route::put('/users/{user}/permissions', [UserPermissionController::class, 'update']);
    Route::post('/users/{user}/permissions/reset', [UserPermissionController::class, 'reset']);


    // Satisfaction Surveys
    Route::get('/satisfaction-surveys/pending', [SatisfactionSurveyController::class, 'pending']);
    Route::get('/satisfaction-surveys/statistics', [SatisfactionSurveyController::class, 'statistics']);
    Route::get('/satisfaction-surveys/check/{ticketId}', [SatisfactionSurveyController::class, 'checkTicket']);
    Route::get('/satisfaction-surveys/ticket/{ticketId}', [SatisfactionSurveyController::class, 'getByTicketId']);
    Route::apiResource('satisfaction-surveys', SatisfactionSurveyController::class);

    // Business Hours
    Route::get('/business-hours/is-open', [BusinessHourController::class, 'isOpen']);
    Route::get('/business-hours/day/{day}', [BusinessHourController::class, 'getByDay']);
    Route::put('/business-hours/bulk', [BusinessHourController::class, 'bulkUpdate']);
    Route::apiResource('business-hours', BusinessHourController::class);

    // Holidays
    Route::get('/holidays/types', [HolidayController::class, 'types']);
    Route::get('/holidays/for-sla', [HolidayController::class, 'forSlaCalculation']);
    Route::get('/holidays/upcoming', [HolidayController::class, 'upcoming']);
    Route::get('/holidays/check/{date}', [HolidayController::class, 'checkDate']);
    Route::get('/holidays/month/{month}', [HolidayController::class, 'byMonth']);
    Route::apiResource('holidays', HolidayController::class);

    // Subcontractors
    Route::get('/subcontractors/all', [SubContractorController::class, 'all']);
    Route::get('/subcontractors/specializations', [SubContractorController::class, 'specializations']);
    Route::get('/subcontractors/specialization/{specialization}', [SubContractorController::class, 'bySpecialization']);
    Route::post('/subcontractors/{subcontractor}/activate', [SubContractorController::class, 'activate']);
    Route::post('/subcontractors/{subcontractor}/deactivate', [SubContractorController::class, 'deactivate']);
    Route::apiResource('subcontractors', SubContractorController::class);

    // System Settings
    Route::post('/system-settings/test-email', [SystemSettingController::class, 'testEmail']);
    Route::get('/system-settings/categories', [SystemSettingController::class, 'categories']);
    Route::get('/system-settings/key/{key}', [SystemSettingController::class, 'getByKey']);
    Route::get('/system-settings/category/{category}', [SystemSettingController::class, 'getByCategory']);
    Route::get('/system-settings/key-value', [SystemSettingController::class, 'asKeyValue']);
    Route::put('/system-settings/bulk', [SystemSettingController::class, 'bulkUpdate']);
    Route::apiResource('system-settings', SystemSettingController::class);

    // Organization Notifications
    Route::post('/organization-notifications/initialize', [OrganizationNotificationController::class, 'initialize']);
    Route::post('/organization-notifications/{id}/test/{channel}', [OrganizationNotificationController::class, 'testNotification']);
    Route::apiResource('organization-notifications', OrganizationNotificationController::class);

    // Preventive Maintenance (PM)
    Route::get('/pm-schedules/statistics', [PmScheduleController::class, 'statistics']);
    Route::post('/pm-schedules/{pmSchedule}/execute', [PmScheduleController::class, 'execute']);
    Route::apiResource('pm-schedules', PmScheduleController::class);

    // PM Projects
    Route::get('/pm-projects/statistics', [PmProjectController::class, 'statistics']);
    Route::apiResource('pm-projects', PmProjectController::class);

    // PM Projects
    Route::apiResource('pm-projects', PmProjectController::class);

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/overview', [DashboardController::class, 'overview']);
        Route::get('/incidents-trend', [DashboardController::class, 'incidentsTrend']);
        Route::get('/incidents-by-category', [DashboardController::class, 'incidentsByCategory']);
        Route::get('/incidents-by-priority', [DashboardController::class, 'incidentsByPriority']);
        Route::get('/top-technicians', [DashboardController::class, 'topTechnicians']);
        Route::get('/recent-incidents', [DashboardController::class, 'recentIncidents']);
        Route::get('/sla-compliance', [DashboardController::class, 'slaCompliance']);
    });

});

/*
|--------------------------------------------------------------------------
| Health Check Routes - Public (ไม่ต้อง Login)
|--------------------------------------------------------------------------
*/
// Health Check endpoint for system monitoring
Route::get('/health', function () {
    try {
        // Check database connection
        DB::connection()->getPdo();
        $dbStatus = 'connected';
    } catch (\Exception $e) {
        $dbStatus = 'disconnected';
    }
    
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'database' => $dbStatus,
        'services' => [
            'api' => 'running',
            'database' => $dbStatus,
        ]
    ]);
});

// Database health check
Route::get('/health/database', function () {
    try {
        $startTime = microtime(true);
        DB::connection()->getPdo();
        DB::select('SELECT 1');
        $responseTime = round((microtime(true) - $startTime) * 1000);
        
        return response()->json([
            'status' => 'ok',
            'connected' => true,
            'responseTime' => $responseTime,
            'timestamp' => now()->toISOString(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'connected' => false,
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString(),
        ], 503);
    }
});

