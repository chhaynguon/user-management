<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\FnctionController;
use App\Http\Controllers\Api\GroupController;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Authenticated routes (all users)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [UserController::class, 'updateProfile']);
});

// Admin-only routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users/me/permissions', [UserController::class, 'currentUserPermissions']);
    Route::get('/users', [UserController::class, 'index'])->middleware('has-permission:USER.VIEW');
    Route::get('/users/{id}', [UserController::class, 'show'])->middleware('permission:USER.VIEW');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:USER.CREATE');
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:USER.UPDATE');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('permission:USER.DELETE');
});

// Route::middleware(['auth:sanctum'])->group(function () {
//     Route::get('/users/me/permissions', [UserController::class, 'currentUserPermissions']);
//     Route::get('/users', [UserController::class, 'index']);
//     Route::get('/users/{id}', [UserController::class, 'show']);
//     Route::post('/users', [UserController::class, 'store']);
//     Route::put('/users/{id}', [UserController::class, 'update']);
//     Route::delete('/users/{id}', [UserController::class, 'destroy']);
// });

Route::prefix('permissions')->group(function () {
    Route::get('/', [PermissionController::class, 'index']);
    Route::get('/{code}', [PermissionController::class, 'show']);
    Route::post('/', [PermissionController::class, 'store']);
    Route::put('/{code}', [PermissionController::class, 'update']);
    Route::delete('/{code}', [PermissionController::class, 'destroy']);
});

Route::prefix('roles')->group(function () {
    Route::get('/', [RoleController::class, 'index']);
    Route::get('/{code}', [RoleController::class, 'show']);
    Route::post('/', [RoleController::class, 'store']);
    Route::put('/{code}', [RoleController::class, 'update']);
    Route::delete('/{code}', [RoleController::class, 'destroy']);
});

Route::prefix('fnctions')->group(function () {
    Route::get('/', [FnctionController::class, 'index']);
    Route::get('/{code}', [FnctionController::class, 'show']);
    Route::post('/', [FnctionController::class, 'store']);
    Route::put('/{code}', [FnctionController::class, 'update']);
    Route::delete('/{code}', [FnctionController::class, 'destroy']);
});

Route::prefix('groups')->group(function () {
    Route::get('/', [GroupController::class, 'index']);
    Route::get('/{code}', [GroupController::class, 'show']);
    Route::post('/', [GroupController::class, 'store']);
    Route::put('/{code}', [GroupController::class, 'update']);
    Route::delete('/{code}', [GroupController::class, 'destroy']);
});
