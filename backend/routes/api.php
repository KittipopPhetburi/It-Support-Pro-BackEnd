<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
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
    NotificationController
};

Route::prefix('v1')->group(function () {

    Route::apiResource('branches', BranchController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('assets', AssetController::class);
    Route::apiResource('incidents', IncidentController::class);
    Route::apiResource('problems', ProblemController::class);
    Route::apiResource('asset-requests', AssetRequestController::class);
    Route::apiResource('other-requests', OtherRequestController::class);
    Route::apiResource('service-catalog-items', ServiceCatalogItemController::class);
    Route::apiResource('service-requests', ServiceRequestController::class);
    Route::apiResource('activity-logs', ActivityLogController::class);
    Route::apiResource('satisfaction-surveys', SatisfactionSurveyController::class);
    Route::apiResource('business-hours', BusinessHourController::class);
    Route::apiResource('holidays', HolidayController::class);
    Route::apiResource('kb-articles', KbArticleController::class);
    Route::apiResource('sub-contractors', SubContractorController::class);
    Route::apiResource('system-settings', SystemSettingController::class);
    Route::apiResource('slas', SlaController::class);
    Route::apiResource('notifications', NotificationController::class);

});
