<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {


    // Auth
    Route::get('/auth/me', function (Request $request) {
        return response()->json($request->user());
    });
    Route::post('/auth/logout', [AuthController::class, 'logout']);


    // Admin-only example
    Route::middleware('role:admin')->group(function () {
        // User routes
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::get('/admin/stats', function () {
            return ['secret' => 'only admin sees this'];
        });
    });

    Route::middleware('role:user')->group(function () {
        Route::get('/profile', [UserController::class, 'updateProfile']);
    });
});
