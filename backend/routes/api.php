<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ItemMachineController;
use App\Http\Controllers\MaintenanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Logout & cek user login
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // CRUD user (khusus admin)
    Route::middleware('role:admin')->apiResource('/users', UserController::class);
});

// User API Routes
Route::middleware(['auth:sanctum', 'role:supervisor,team_leader'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Item Machines hanya untuk team_leader
Route::middleware(['auth:sanctum', 'role:team_leader'])->group(function () {
    Route::apiResource('item-machines', ItemMachineController::class);
});

// Maintenance API Routes
Route::middleware(['auth:sanctum', 'role:team_leader'])->group(function () {
    Route::get('/activity-tms', [MaintenanceController::class, 'getActivityTms']);
    Route::post('/activity-tms', [MaintenanceController::class, 'storeActivityTms']);
    Route::get('/maintenance-types', [MaintenanceController::class, 'getMaintenanceTypes']);
    Route::post('/maintenance', [MaintenanceController::class, 'storeMaintenance']);
});
