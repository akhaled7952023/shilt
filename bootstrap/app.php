<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web'])
                ->group(base_path('routes/dashboard.php'));
            Route::middleware(['web'])
                ->group(base_path('routes/sitemap.php'));
            Route::middleware(['web'])
                ->group(base_path('routes/master-data.php'));
            Route::middleware(['web'])
                ->group(base_path('routes/delegates.php'));
            Route::middleware(['web'])
                ->group(base_path('routes/vehicles.php'));
            Route::middleware(['web'])
                ->group(base_path('routes/monthly.php'));
            Route::middleware(['web'])
                ->group(base_path('routes/reports.php'));
            Route::middleware(['web'])
                ->group(base_path('routes/delegate-portal.php'));
            Route::middleware(['web'])
                ->group(base_path('routes/support-dashboard.php'));
            Route::middleware(['web'])
                ->group(base_path('routes/support-portal.php'));
        },
    )

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(function () {
            if (request()->is('dashboard') || request()->is('dashboard/*')) {
                return route('dashboard.login');
            }
            if (request()->is('portal') || request()->is('portal/*')) {
                return route('portal.login');
            }
            return route('dashboard.login');
        });

        $middleware->redirectUsersTo(function () {
            if (Auth::guard('web')->check()) {
                return route('dashboard.welcome');
            }
            if (Auth::guard('delegate')->check()) {
                return route('portal.dashboard');
            }
            return '/';
        });

        $middleware->alias([
            /**** OTHER MIDDLEWARE ALIASES ****/
            'permission'     => \App\Http\Middleware\CheckPermission::class,
            'portal.changed' => \App\Http\Middleware\EnsurePortalPasswordChanged::class,
            'portal.locale'  => \App\Http\Middleware\SetPortalLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
