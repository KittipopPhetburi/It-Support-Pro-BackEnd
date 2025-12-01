<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    BranchController,
    DepartmentController,
    UserController,
    AssetController,
    IncidentController,
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
    SlaController,
    NotificationController,
    DashboardController
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

    // Branches
    Route::get('/branches/all', [BranchController::class, 'all']);
    Route::apiResource('branches', BranchController::class);

    // Departments
    Route::get('/departments/all', [DepartmentController::class, 'all']);
    Route::apiResource('departments', DepartmentController::class);

    // Assets
    Route::get('/assets/statistics', [AssetController::class, 'statistics']);
    Route::apiResource('assets', AssetController::class);

    // Incidents
    Route::get('/incidents/statistics', [IncidentController::class, 'statistics']);
    Route::get('/incidents/my', [IncidentController::class, 'myIncidents']);
    Route::get('/incidents/assigned', [IncidentController::class, 'assignedIncidents']);
    Route::post('/incidents/{incident}/assign', [IncidentController::class, 'assign']);
    Route::apiResource('incidents', IncidentController::class);

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
    Route::apiResource('other-requests', OtherRequestController::class);

    // Service Catalog
    Route::get('/service-catalog/all', [ServiceCatalogItemController::class, 'all']);
    Route::get('/service-catalog/categories', [ServiceCatalogItemController::class, 'categories']);
    Route::apiResource('service-catalog', ServiceCatalogItemController::class);

    // Service Requests
    Route::get('/service-requests/statistics', [ServiceRequestController::class, 'statistics']);
    Route::get('/service-requests/my', [ServiceRequestController::class, 'myRequests']);
    Route::apiResource('service-requests', ServiceRequestController::class);

    // SLAs
    Route::get('/slas/all', [SlaController::class, 'all']);
    Route::get('/slas/priority/{priority}', [SlaController::class, 'getByPriority']);
    Route::apiResource('slas', SlaController::class);

    // Knowledge Base
    Route::get('/kb-articles/popular', [KbArticleController::class, 'popular']);
    Route::get('/kb-articles/recent', [KbArticleController::class, 'recent']);
    Route::get('/kb-articles/categories', [KbArticleController::class, 'categories']);
    Route::post('/kb-articles/{kbArticle}/helpful', [KbArticleController::class, 'helpful']);
    Route::post('/kb-articles/{kbArticle}/not-helpful', [KbArticleController::class, 'notHelpful']);
    Route::apiResource('kb-articles', KbArticleController::class);

    // Activity Logs
    Route::get('/activity-logs/my', [ActivityLogController::class, 'myLogs']);
    Route::get('/activity-logs/actions', [ActivityLogController::class, 'actions']);
    Route::get('/activity-logs/modules', [ActivityLogController::class, 'modules']);
    Route::delete('/activity-logs/clear-old', [ActivityLogController::class, 'clearOldLogs']);
    Route::apiResource('activity-logs', ActivityLogController::class);

    // Notifications
    Route::get('/notifications/my', [NotificationController::class, 'myNotifications']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll']);
    Route::apiResource('notifications', NotificationController::class);

    // Satisfaction Surveys
    Route::get('/satisfaction-surveys/statistics', [SatisfactionSurveyController::class, 'statistics']);
    Route::get('/satisfaction-surveys/check/{ticketId}', [SatisfactionSurveyController::class, 'checkTicket']);
    Route::apiResource('satisfaction-surveys', SatisfactionSurveyController::class);

    // Business Hours
    Route::get('/business-hours/is-open', [BusinessHourController::class, 'isOpen']);
    Route::get('/business-hours/day/{day}', [BusinessHourController::class, 'getByDay']);
    Route::put('/business-hours/bulk', [BusinessHourController::class, 'bulkUpdate']);
    Route::apiResource('business-hours', BusinessHourController::class);

    // Holidays
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
    Route::get('/system-settings/categories', [SystemSettingController::class, 'categories']);
    Route::get('/system-settings/key/{key}', [SystemSettingController::class, 'getByKey']);
    Route::get('/system-settings/category/{category}', [SystemSettingController::class, 'getByCategory']);
    Route::get('/system-settings/key-value', [SystemSettingController::class, 'asKeyValue']);
    Route::put('/system-settings/bulk', [SystemSettingController::class, 'bulkUpdate']);
    Route::apiResource('system-settings', SystemSettingController::class);

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
