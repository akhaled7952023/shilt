<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::guard('web')->check()) {
        return redirect()->route('dashboard.welcome');
    }
    if (Auth::guard('delegate')->check()) {
        return redirect()->route('portal.dashboard');
    }
    return redirect()->route('portal.login');
});
