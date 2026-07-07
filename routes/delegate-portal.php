<?php

use App\Http\Controllers\Delegate\Auth\DelegateAuthController;
use App\Http\Controllers\Delegate\DelegateDashboardController;
use App\Http\Controllers\Delegate\DelegateNotificationController;
use App\Http\Controllers\Delegate\DelegateProfileController;
use App\Http\Controllers\Delegate\DelegateSettlementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'portal.locale'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function () {

        // ── Language switch (available to all portal visitors) ─────────────────
        Route::get('lang/{locale}', function (string $locale) {
            if (in_array($locale, ['ar', 'en'])) {
                session(['portal_locale' => $locale]);
            }
            return redirect()->back()->withInput();
        })->name('lang');

        // ── Guest-only routes ─────────────────────────────────────────────────
        Route::middleware('guest:delegate')->group(function () {
            Route::get('login', [DelegateAuthController::class, 'showLoginForm'])->name('login');
            Route::post('login', [DelegateAuthController::class, 'login'])->name('login.post');
        });

        // ── Authenticated routes ───────────────────────────────────────────────
        Route::middleware('auth:delegate')->group(function () {
            Route::post('logout', [DelegateAuthController::class, 'logout'])->name('logout');

            // First-login password change (exempt from portal.changed middleware)
            Route::get('change-password', [DelegateAuthController::class, 'showChangePassword'])->name('change-password');
            Route::post('change-password', [DelegateAuthController::class, 'changePassword'])->name('change-password.post');

            // All other portal pages require password to have been changed
            Route::middleware('portal.changed')->group(function () {
                Route::get('dashboard', [DelegateDashboardController::class, 'index'])->name('dashboard');

                Route::get('profile', [DelegateProfileController::class, 'show'])->name('profile');
                Route::post('profile/password', [DelegateProfileController::class, 'changePassword'])->name('profile.password');

                Route::get('settlements', [DelegateSettlementController::class, 'index'])->name('settlements.index');
                Route::get('settlements/{period}/print', [DelegateSettlementController::class, 'printView'])->name('settlements.print');
                Route::get('settlements/{period}', [DelegateSettlementController::class, 'show'])->name('settlements.show');

                Route::get('notifications', [DelegateNotificationController::class, 'index'])->name('notifications.index');
                Route::post('notifications/read-all', [DelegateNotificationController::class, 'markAllRead'])->name('notifications.read-all');
                Route::post('notifications/{notification}/read', [DelegateNotificationController::class, 'markRead'])->name('notifications.read');
            });
        });
    });
