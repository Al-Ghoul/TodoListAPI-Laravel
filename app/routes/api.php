<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TodoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::post('/profile', [AuthController::class, 'profile'])->middleware('auth:api');
});

Route::middleware(['throttle:todos'])->prefix('todos')->group(
    function () {

        Route::get('', [TodoController::class, 'index']);
        Route::post('', [TodoController::class, 'store'])->middleware('auth:api');
        Route::patch('{id}', [TodoController::class, 'update'])->middleware('auth:api');
        Route::delete('{id}', [TodoController::class, 'destroy'])->middleware('auth:api');

        Route::fallback(function () {
            return response()->json([
                'message' => 'Too many requests. Please wait before retrying.',
            ], 429);
        });
    }
);
