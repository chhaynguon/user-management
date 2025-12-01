<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     */
    public function handle($request, Closure $next, $permission)
{
    $user = $request->user();
    if (!$user || !$user->hasPermission($permission)) {
        return response()->json(['message' => 'Forbidden'], 403);
    }
    return $next($request);
}

}
