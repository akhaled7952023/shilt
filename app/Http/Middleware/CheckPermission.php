<?php

namespace App\Http\Middleware;

use Closure;

class CheckPermission
{
    public function handle($request, Closure $next, $permission)
    {
        $user = auth()->user();
        $permissions = $user->role->permissions ?? [];

        if (! is_array($permissions)) {
            $permissions = json_decode($permissions, true) ?? [];
        }

        if (! in_array($permission, $permissions)) {
            abort(403);
        }

        return $next($request);
    }
}

