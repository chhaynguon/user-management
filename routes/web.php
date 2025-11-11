<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function(){
//     return view('auth/login');
// });

    Route::get('/{any}', function () {
        return view('app');
    })->where('any', '^(?!api|sanctum).*$');
