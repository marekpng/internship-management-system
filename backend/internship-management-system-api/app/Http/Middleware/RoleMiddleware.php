<?php

namespace app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user(); // získame aktuálneho prihláseného používateľa

        if (!$user || !$user->role || strtolower($user->role) !== strtolower($role)) {
            return response()->json([
                'message' => 'Nemáte oprávnenie pristupovať k tomuto zdroju.'
            ], 403);
        }

        return $next($request);
    }
}
