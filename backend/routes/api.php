<?php

use App\Http\Controllers\ActivityTmsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FawReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ItemMachineController;
use App\Http\Controllers\LeakageReportController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StockSparepartController;
use App\Http\Controllers\TmsSparepartController;
use App\Http\Controllers\PicaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CatatanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    // Logout & cek user login
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // CRUD user (khusus admin)
    Route::apiResource('/users', UserController::class);
});

// User API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Item Machines — semua role terautentikasi
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('item-machines', ItemMachineController::class);
});

// Maintenance / Activity TMS — semua role terautentikasi
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/activity-tms-all', [ActivityTmsController::class, 'getAllActivityTms']);
    Route::get('/activity-tms/{id}', [ActivityTmsController::class, 'getActivityTmsById']);
    Route::post('/activity-tms', [ActivityTmsController::class, 'storeActivityTms']);
    Route::delete('/activity-tms/{id}', [ActivityTmsController::class, 'destroyActivityTms']);
    Route::get('/maintenance-types', [ActivityTmsController::class, 'getMaintenanceTypes']);
    Route::post('/maintenance', [ActivityTmsController::class, 'storeMaintenance']);
    Route::post('/activity-tms-update/{id}', [ActivityTmsController::class, 'updateActivityTms']);
    Route::put('/activity-tms/{id}/catatan', [CatatanController::class, 'update']);
    Route::get('/export-activity-tms', [ActivityTmsController::class, 'export']);
});

// FAW REPORT — semua role terautentikasi
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/faw-reports', [FawReportController::class, 'index']);
    Route::get('/faw-reports/export', [FawReportController::class, 'export']);
    Route::get('/faw-reports/{id}', [FawReportController::class, 'show']);
    Route::post('/faw-reports', [FawReportController::class, 'store']);
    Route::post('/faw-reports-update/{id}', [FawReportController::class, 'update']);
    Route::delete('/faw-reports/{id}', [FawReportController::class, 'destroy']);
});

Route::prefix('leakage-reports')->group(function () {
    Route::get('/', [LeakageReportController::class, 'index']);
    Route::post('/', [LeakageReportController::class, 'store']);
    Route::get('/{id}', [LeakageReportController::class, 'show']);
    Route::post('/{id}', [LeakageReportController::class, 'update']);
    Route::delete('/{id}', [LeakageReportController::class, 'destroy']);
});

// Schedule
Route::get('/activity-summary', [ScheduleController::class, 'index']);
Route::get('/export-pm-schedule', [ScheduleController::class, 'export']);
Route::post('/getActivityByScheduleList', [ActivityTMSController::class, 'getActivityByScheduleList']);

// Sparepart
Route::prefix('spareparts')->group(function () {
    Route::get('/', [StockSparepartController::class, 'index']);
    Route::post('/', [StockSparepartController::class, 'store']);
    Route::get('/export', [StockSparepartController::class, 'export']);
    Route::get('/{id}', [StockSparepartController::class, 'show']);
    Route::put('/{id}', [StockSparepartController::class, 'update']);
    Route::delete('/{id}', [StockSparepartController::class, 'destroy']);
    Route::get('/{id}/logs', [StockSparepartController::class, 'getLogs']);
});

// Semua riwayat sparepart
Route::get('/sparepart-logs', [StockSparepartController::class, 'getAllLogs']);

// TMS Sparepart
Route::prefix('tmssparepart')->group(function () {
    Route::get('/', [TmsSparepartController::class, 'index']);
    Route::post('/', [TmsSparepartController::class, 'store']);
    Route::delete('/{id}', [TmsSparepartController::class, 'destroy']);
});

// Pica
Route::prefix('picas')->group(function () {
    Route::get('/', [PicaController::class, 'index']);
    Route::get('/{id}', [PicaController::class, 'show']);
    Route::post('/', [PicaController::class, 'store']);
    Route::put('/{id}', [PicaController::class, 'update']);
    Route::delete('/{id}', [PicaController::class, 'destroy']);
});

// Monitoring dashboard
Route::get('/dashboard-statistics', [DashboardController::class, 'index']);
