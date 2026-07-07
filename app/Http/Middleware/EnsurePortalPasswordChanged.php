<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePortalPasswordChanged
{
    public function handle(Request $request, Closure $next): mixed
    {
        $delegate = auth('delegate')->user();

        if ($delegate && $delegate->portal_first_login) {
            if (!$request->routeIs('portal.change-password') && !$request->routeIs('portal.change-password.post') && !$request->routeIs('portal.logout')) {
                return redirect()->route('portal.change-password')
                    ->with('info', 'يجب تغيير كلمة المرور قبل المتابعة.');
            }
        }

        return $next($request);
    }
}
