<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\TipController;
use App\Http\Controllers\NotificationController;

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminComplaintController;
use App\Http\Controllers\Admin\AdminTipController;
use App\Http\Controllers\Admin\PlantReferenceController;
use App\Http\Controllers\Admin\AdminNotificationController;


/*
|--------------------------------------------------------------------------
| Auth (OTP)
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/signup/request-otp', [AuthController::class, 'signupRequestOtp']);
    Route::post('/signup/verify', [AuthController::class, 'signupVerify']);

    Route::post('/login/request-otp', [AuthController::class, 'loginRequestOtp']);
    Route::post('/login/verify', [AuthController::class, 'loginVerify']);

    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| Mobile User APIs (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'active'])->group(function () {

    // Plants CRUD
    Route::get('/plants', [PlantController::class, 'index']);
    Route::get('/plants/{plant}', [PlantController::class, 'show']);
    Route::post('/plants', [PlantController::class, 'store']);
    Route::put('/plants/{plant}', [PlantController::class, 'update']);
    Route::delete('/plants/{plant}', [PlantController::class, 'destroy']);

    // Plant Logs
    Route::post('/plants/{plant}/logs', [PlantController::class, 'storeLog']);
    Route::get('/plants/{plant}/logs', [PlantController::class, 'indexLogs']);
    Route::put('/plants/{plant}/logs/{log}', [PlantController::class, 'updateLog']);
    Route::delete('/plants/{plant}/logs/{log}', [PlantController::class, 'destroyLog']);

    // Complaints (User)
    Route::get('/complaints', [ComplaintController::class, 'index']);
    Route::post('/complaints', [ComplaintController::class, 'store']);
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show']);
    Route::put('/complaints/{complaint}', [ComplaintController::class, 'update']);
    Route::delete('/complaints/{complaint}', [ComplaintController::class, 'destroy']);

    // Tips (User Read-only)
    Route::get('/tips', [TipController::class, 'index']);
    Route::get('/tips/{tip}', [TipController::class, 'show']);

    // Notifications (User)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::get('/notifications/today', [NotificationController::class, 'today']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Admin APIs (Protected)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'active', 'role:admin'])
    ->group(function () {

        // Users
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::put('/users/{user}', [AdminUserController::class, 'update']);
        Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole']);
        Route::put('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive']);

        // Complaints
        Route::get('/complaints', [AdminComplaintController::class, 'index']);
        Route::get('/complaints/{complaint}', [AdminComplaintController::class, 'show']);
        Route::put('/complaints/{complaint}/status', [AdminComplaintController::class, 'updateStatus']);

        // Tips (Admin CRUD)
        Route::get('/tips', [AdminTipController::class, 'index']);
        Route::get('/tips/{tip}', [AdminTipController::class, 'show']);
        Route::post('/tips', [AdminTipController::class, 'store']);
        Route::put('/tips/{tip}', [AdminTipController::class, 'update']);
        Route::delete('/tips/{tip}', [AdminTipController::class, 'destroy']);

        // Plant References
        Route::get('/plant-references', [PlantReferenceController::class, 'index']);
        Route::get('/plant-references/{plantReference}', [PlantReferenceController::class, 'show']);
        Route::post('/plant-references', [PlantReferenceController::class, 'store']);
        Route::put('/plant-references/{plantReference}', [PlantReferenceController::class, 'update']);
        Route::delete('/plant-references/{plantReference}', [PlantReferenceController::class, 'destroy']);

        //Admin notifications  
        Route::post('/notifications/users/{user}', [AdminNotificationController::class, 'createNotificationForUser']);
        Route::post('/notifications/broadcast', [AdminNotificationController::class, 'broadcastNotification']);
    });
