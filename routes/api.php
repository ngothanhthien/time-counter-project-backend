<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Middleware\UserOwnProjectMiddleware;
use App\Http\Controllers\ProjectNoteController;
use App\Http\Controllers\ProjectTimeController;

// user routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('jwt')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);

    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::post('/', action: [ProjectController::class, 'store']);
        Route::middleware(UserOwnProjectMiddleware::class)->group(function () {
            Route::get('/{id}', [ProjectController::class, 'show']);
            Route::put('/{id}', [ProjectController::class, 'update']);
            Route::delete('/{id}', [ProjectController::class, 'destroy']);

            Route::prefix('notes')->group(function () {
                Route::post('/', [ProjectNoteController::class, 'store']);
                Route::put('/{id}', [ProjectNoteController::class, 'update']);
                Route::delete('/{id}', [ProjectNoteController::class, 'destroy']);
            });

            Route::prefix('time')->group(function () {
                Route::post('/', [ProjectTimeController::class, 'store']);
                Route::put('/{id}', [ProjectTimeController::class, 'update']);
                Route::delete('/{id}', [ProjectTimeController::class, 'destroy']);
            });
        });
    });
});
