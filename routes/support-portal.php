<?php

use App\Http\Controllers\Delegate\Support\PortalSupportTicketController;
use Illuminate\Support\Facades\Route;

// Phase 3 — HungerStation Support Center (delegate portal side)
//
// NOTE: Portal notification routes (/portal/support/notifications/*) are
// intentionally excluded. The existing /portal/notifications/* routes in
// delegate-portal.php serve DelegateNotificationController.
// Phase 3 portal notifications will be registered in Batch 6 after resolving
// the route naming and prefix conflict.
Route::middleware(['web', 'portal.locale', 'auth:delegate', 'portal.changed'])
    ->prefix('portal/support')
    ->name('portal.support.')
    ->group(function () {

        // ── Delegate Tickets ──────────────────────────────────────────────────

        Route::get('tickets', [PortalSupportTicketController::class, 'index'])
            ->name('tickets.index');

        Route::get('tickets/create', [PortalSupportTicketController::class, 'create'])
            ->name('tickets.create');

        // Rate limit: 5 new tickets per 24 hours per delegate
        Route::post('tickets', [PortalSupportTicketController::class, 'store'])
            ->middleware('throttle:5,1440')
            ->name('tickets.store');

        Route::get('tickets/{ticket}', [PortalSupportTicketController::class, 'show'])
            ->name('tickets.show');

        // Rate limit: 20 replies per 60 minutes per delegate
        Route::post('tickets/{ticket}/reply', [PortalSupportTicketController::class, 'reply'])
            ->middleware('throttle:20,60')
            ->name('tickets.reply');

        // Attachment download — ownership verified inside the controller
        Route::get('tickets/{ticket}/attachments/{attachment}/download',
            [PortalSupportTicketController::class, 'downloadAttachment'])
            ->name('tickets.attachments.download');
    });
