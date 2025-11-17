<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$user->roles()->pluck('code')->intersect($roles)->count()) {
            return response()->json([
                'message' => 'Unauthorized. You do not have the required role.'
            ], 403);
        }

        return $next($request);
    }
}
