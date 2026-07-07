<?php

use App\Http\Controllers\Dashboard\Delegates\DelegateController;
use App\Http\Controllers\Dashboard\Delegates\DelegateDocumentController;
use App\Http\Controllers\Dashboard\Delegates\DelegateLeaveController;
use App\Http\Controllers\Dashboard\Notes\DelegateNoteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'permission:delegates'])
    ->prefix('dashboard/delegates')
    ->name('dashboard.delegates.')
    ->group(function () {
        Route::resource('/', DelegateController::class)->parameters(['' => 'delegate']);
        Route::patch('{delegate}/status', [DelegateController::class, 'updateStatus'])
            ->name('update-status');
        Route::patch('{delegate}/password', [DelegateController::class, 'updatePassword'])
            ->name('update-password');
        Route::get('{delegate}/profile', [DelegateController::class, 'profile'])
            ->name('profile');
        Route::resource('{delegate}/documents', DelegateDocumentController::class)
            ->only(['index', 'store', 'destroy']);
        Route::post('{delegate}/notes', [DelegateNoteController::class, 'store'])
            ->name('notes.store');
        Route::delete('{delegate}/notes/{note}', [DelegateNoteController::class, 'destroy'])
            ->name('notes.destroy');
        // Portal management
        Route::post('{delegate}/portal/enable', [DelegateController::class, 'portalEnable'])
            ->name('portal.enable');
        Route::post('{delegate}/portal/generate', [DelegateController::class, 'portalGenerateCredentials'])
            ->name('portal.generate');
        Route::post('{delegate}/portal/reset-password', [DelegateController::class, 'portalResetPassword'])
            ->name('portal.reset-password');
        Route::post('{delegate}/portal/disable', [DelegateController::class, 'portalDisable'])
            ->name('portal.disable');
        Route::post('{delegate}/portal/announce', [DelegateController::class, 'portalAnnounce'])
            ->name('portal.announce');
        // Leaves
        Route::post('{delegate}/leaves', [DelegateLeaveController::class, 'store'])
            ->name('leaves.store');
        Route::get('{delegate}/leaves/{leave}/edit', [DelegateLeaveController::class, 'edit'])
            ->name('leaves.edit');
        Route::put('{delegate}/leaves/{leave}', [DelegateLeaveController::class, 'update'])
            ->name('leaves.update');
        Route::delete('{delegate}/leaves/{leave}', [DelegateLeaveController::class, 'destroy'])
            ->name('leaves.destroy');
    });
