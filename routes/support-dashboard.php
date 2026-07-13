<?php

use App\Http\Controllers\Dashboard\Support\ActivityFeedController;
use App\Http\Controllers\Dashboard\Support\AdminNotificationController;
use App\Http\Controllers\Dashboard\Support\FinancialRequestController;
use App\Http\Controllers\Dashboard\Support\PendingFinancialEntryController;
use App\Http\Controllers\Dashboard\Support\SupportTicketAttachmentController;
use App\Http\Controllers\Dashboard\Support\SupportTicketController;
use Illuminate\Support\Facades\Route;

// Phase 3 — HungerStation Support Center (admin/dashboard side)
Route::middleware(['web', 'auth', 'permission:support'])
    ->prefix('dashboard/support')
    ->name('dashboard.support.')
    ->group(function () {

        // ── Tickets ───────────────────────────────────────────────────────────
        Route::get('tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
        Route::get('tickets/{ticket}', [SupportTicketController::class, 'show'])->name('tickets.show');
        Route::post('tickets/{ticket}/assign', [SupportTicketController::class, 'assign'])->name('tickets.assign');
        Route::patch('tickets/{ticket}/priority', [SupportTicketController::class, 'updateStatus'])->name('tickets.priority');
        Route::post('tickets/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('tickets.reply');
        Route::patch('tickets/{ticket}/resolve', [SupportTicketController::class, 'close'])->name('tickets.resolve');
        Route::patch('tickets/{ticket}/close', [SupportTicketController::class, 'reopen'])->name('tickets.close');
        Route::get('objections', [SupportTicketController::class, 'objections'])->name('tickets.objections');

        // ── Attachments ───────────────────────────────────────────────────────
        Route::get('tickets/{ticket}/attachments/{attachment}/download',
            [SupportTicketAttachmentController::class, 'download'])
            ->name('tickets.attachments.download');

        // ── Financial Requests ────────────────────────────────────────────────
        Route::get('financial-requests', [FinancialRequestController::class, 'index'])->name('financial-requests.index');
        Route::get('financial-requests/{financialRequest}', [FinancialRequestController::class, 'show'])->name('financial-requests.show');
        Route::patch('financial-requests/{financialRequest}/approve', [FinancialRequestController::class, 'approve'])->name('financial-requests.approve');
        Route::patch('financial-requests/{financialRequest}/reject', [FinancialRequestController::class, 'reject'])->name('financial-requests.reject');
        Route::patch('financial-requests/{financialRequest}/request-info', [FinancialRequestController::class, 'requestMoreInfo'])->name('financial-requests.request-info');

        // ── Pending Financial Entries ─────────────────────────────────────────
        Route::get('pending-entries', [PendingFinancialEntryController::class, 'index'])->name('pending-entries.index');
        Route::get('pending-entries/period/{period}', [PendingFinancialEntryController::class, 'byPeriod'])->name('pending-entries.by-period');
        Route::patch('pending-entries/{entry}/import', [PendingFinancialEntryController::class, 'markImported'])->name('pending-entries.import');
        Route::patch('pending-entries/{entry}/cancel', [PendingFinancialEntryController::class, 'cancel'])->name('pending-entries.cancel');

        // ── Activity Feed ─────────────────────────────────────────────────────
        Route::get('activity', [ActivityFeedController::class, 'index'])->name('activity.index');
        Route::get('activity/latest', [ActivityFeedController::class, 'latest'])->name('activity.latest');

        // ── Admin Notification Inbox ──────────────────────────────────────────
        Route::get('notifications', [AdminNotificationController::class, 'inbox'])->name('notifications.inbox');
        Route::get('notifications/unread-count', [AdminNotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('notifications/read-all', [AdminNotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('notifications/{id}/read', [AdminNotificationController::class, 'markRead'])->name('notifications.read');

    });
