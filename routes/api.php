<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClinicalAlertController;
use Illuminate\Support\Facades\Route;

Route::middleware('tenant')->group(function (): void {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
});

Route::middleware(['tenant', 'jwt.auth'])->group(function (): void {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/patients', [ClinicalAlertController::class, 'patientsIndex']);
    Route::post('/patients', [ClinicalAlertController::class, 'patientsStore']);

    Route::get('/clinical-records', [ClinicalAlertController::class, 'recordsIndex']);
    Route::post('/clinical-records', [ClinicalAlertController::class, 'recordsStore']);

    Route::get('/clinical-records/alerts/summary', [ClinicalAlertController::class, 'alertsSummary']);
    Route::get('/clinical-records/alerts/active', [ClinicalAlertController::class, 'activeAlerts']);

    Route::get('/clinical-ranges', [ClinicalAlertController::class, 'rangesIndex']);
});

Route::middleware(['tenant', 'jwt.refresh'])->group(function (): void {
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
});
