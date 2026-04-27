<?php

use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\KDSController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ShiftController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

    // Auth
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.login');

    // Attendance (PIN-based, no JWT needed)
    Route::prefix('attendance')->group(function () {
        Route::post('/clock-in',  [AttendanceController::class, 'clockIn']);
        Route::post('/clock-out', [AttendanceController::class, 'clockOut']);
    });

    /*
    |--------------------------------------------------------------------------
    | Authenticated Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'verified.active'])->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me',     [AuthController::class, 'me']);

        // POS Menu
        Route::get('/menu', [ProductController::class, 'menu']);

        // Shift
        Route::prefix('shifts')->group(function () {
            Route::get('/current',    [ShiftController::class, 'current']);
            Route::post('/open',      [ShiftController::class, 'open']);
            Route::post('/close',     [ShiftController::class, 'close']);
            Route::get('/{shift}/summary', [ShiftController::class, 'summary']);
        });

        // Orders
        Route::prefix('orders')->group(function () {
            Route::get('/',                          [OrderController::class, 'index']);
            Route::post('/',                         [OrderController::class, 'store']);
            Route::get('/{order}',                   [OrderController::class, 'show']);
            Route::post('/{order}/items',            [OrderController::class, 'addItem']);
            Route::delete('/{order}/items/{itemId}', [OrderController::class, 'removeItem']);
            Route::post('/{order}/hold',             [OrderController::class, 'hold']);
            Route::post('/{order}/checkout',         [OrderController::class, 'checkout']);
            Route::get('/{order}/receipt',           [OrderController::class, 'receipt']);
        });

        // KDS (Kitchen Display System) — accessible by barista/crew
        Route::prefix('kds')->middleware('role:barista|kasir|admin|super_admin')->group(function () {
            Route::get('/',                                        [KDSController::class, 'index']);
            Route::patch('/{orderId}/items/{itemId}/status',      [KDSController::class, 'updateItemStatus']);
            Route::post('/{orderId}/complete',                    [KDSController::class, 'markOrderComplete']);
            Route::post('/{orderId}/serve',                       [KDSController::class, 'serveOrder']);
        });

    });
});
