<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        return response()->json([
            'message' => 'Welcome to Admin Dashboard',
            'user' => $request->user(), // this shows the logged-in admin info
        ]);
    }
}
