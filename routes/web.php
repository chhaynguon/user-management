<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('auth/login');
})->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
});

