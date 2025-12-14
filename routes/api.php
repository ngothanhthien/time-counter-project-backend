<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Middleware\UserOwnProjectMiddleware;
use App\Http\Controllers\ProjectNoteController;
use App\Http\Controllers\ProjectTimeController;
use App\Http\Controllers\SettingController;
use App\Http\Middleware\TimerBelongToProjectMiddleware;
use App\Http\Middleware\NoteBelongToProjectMiddleware;

// ping route
Route::get('/ping', function () {
    return response()->json([
        'message' => 'pong',
    ]);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);

    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::post('/', action: [ProjectController::class, 'store']);
        Route::middleware(UserOwnProjectMiddleware::class)->group(function () {
            Route::get('/{id}', [ProjectController::class, 'show']);
            Route::put('/{id}', [ProjectController::class, 'update']);
            Route::delete('/{id}', [ProjectController::class, 'destroy']);

            Route::middleware(NoteBelongToProjectMiddleware::class)->group(function () {
                Route::prefix('notes')->group(function () {
                    Route::post('/', [ProjectNoteController::class, 'store']);
                    Route::put('/{id}', [ProjectNoteController::class, 'update']);
                    Route::delete('/{id}', [ProjectNoteController::class, 'destroy']);
                });
            });

            Route::prefix('time')->group(function () {
                Route::post('/', [ProjectTimeController::class, 'store']);
                Route::middleware(TimerBelongToProjectMiddleware::class)->group(function () {
                    Route::put('/{id}/start', [ProjectTimeController::class, 'start']);
                    Route::put('/{id}/stop', [ProjectTimeController::class, 'stop']);
                    Route::put('/{id}/update-time', [ProjectTimeController::class, 'updateTime']);
                    Route::delete('/{id}', [ProjectTimeController::class, 'destroy']);
                });
            });
        });
    });

    Route::get('/settings', [SettingController::class, 'index']);
});
