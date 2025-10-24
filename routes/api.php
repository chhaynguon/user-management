<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // example user routes:
    Route::get('/users', function () {
        return \App\Models\User::all();
    });

    // admin-only example
    Route::middleware('admin')->group(function () {
        Route::get('/admin/stats', function () {
            return ['secret' => 'only admin sees this'];
        });
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']);      // List all users
    Route::get('/users/{id}', [UserController::class, 'show']);  // Get a single user
    Route::post('/users', [UserController::class, 'store']);     // Create a user
    Route::put('/users/{id}', [UserController::class, 'update']); // Update a user
    Route::delete('/users/{id}', [UserController::class, 'destroy']); // Delete a user
});
