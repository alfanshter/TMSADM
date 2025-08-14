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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Logout & cek user login
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // CRUD user (khusus admin)
    Route::middleware('role:admin')->apiResource('/users', UserController::class);
});

// User API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Item Machines hanya untuk team_leader
Route::middleware(['auth:sanctum', 'role:team_leader,admin'])->group(function () {
    Route::apiResource('item-machines', ItemMachineController::class);
});

// Maintenance API Routes
Route::middleware(['auth:sanctum', 'role:team_leader,admin'])->group(function () {
    Route::get('/activity-tms-all', [ActivityTmsController::class, 'getAllActivityTms']);
    Route::get('/activity-tms/{id}', [ActivityTmsController::class, 'getActivityTmsById']);
    Route::post('/activity-tms', [ActivityTmsController::class, 'storeActivityTms']);
    Route::delete('/activity-tms/{id}', [ActivityTmsController::class, 'destroyActivityTms']);
    Route::get('/maintenance-types', [ActivityTmsController::class, 'getMaintenanceTypes']);
    Route::post('/maintenance', [ActivityTmsController::class, 'storeMaintenance']);
    Route::post('/activity-tms-update/{id}', [ActivityTmsController::class, 'updateActivityTms']);
});

//FAW REPORT
Route::middleware(['auth:sanctum', 'role:team_leader,admin'])->group(function () {
    Route::apiResource('faw-reports', FawReportController::class);
});

Route::prefix('leakage-reports')->group(function () {
    Route::get('/', [LeakageReportController::class, 'index']);
    Route::post('/', [LeakageReportController::class, 'store']);
    Route::get('/{id}', [LeakageReportController::class, 'show']);
    Route::post('/{id}', [LeakageReportController::class, 'update']); // Bisa juga pakai PUT
    Route::delete('/{id}', [LeakageReportController::class, 'destroy']);
});

//Schedule
Route::get('/activity-summary', [ScheduleController::class, 'index']);
