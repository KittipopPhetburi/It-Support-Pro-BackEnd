<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\IncidentController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    Route::apiResource('incidents', IncidentController::class);

    // Assets
    Route::apiResource('assets', \App\Http\Controllers\API\AssetController::class);
    Route::post('assets/{asset}/assign', [\App\Http\Controllers\API\AssetController::class, 'assign']);
    Route::post('assets/{asset}/unassign', [\App\Http\Controllers\API\AssetController::class, 'unassign']);

    // KB Articles
    Route::apiResource('kb-articles', \App\Http\Controllers\API\KbArticleController::class);
    Route::post('kb-articles/{kbArticle}/rate', [\App\Http\Controllers\API\KbArticleController::class, 'rate']);

    // Problems
    Route::apiResource('problems', \App\Http\Controllers\API\ProblemController::class);
    Route::post('problems/{problem}/incidents', [\App\Http\Controllers\API\ProblemController::class, 'attachIncident']);
    Route::delete('problems/{problem}/incidents', [\App\Http\Controllers\API\ProblemController::class, 'detachIncident']);

    // Requests
    Route::post('asset-requests', [\App\Http\Controllers\API\AssetRequestController::class, 'store']);
    Route::post('other-requests', [\App\Http\Controllers\API\OtherRequestController::class, 'store']);
});
