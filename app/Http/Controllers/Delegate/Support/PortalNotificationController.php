<?php

namespace App\Http\Controllers\Delegate\Support;

use App\Http\Controllers\Controller;
use App\Services\Support\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Portal notification controller for delegates.
 *
 * NOTE (Stage A): Routes for this controller are NOT registered in Stage A
 * because /portal/notifications/* conflicts with existing routes in
 * routes/delegate-portal.php. Route registration is deferred to Stage B.
 */
class PortalNotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function index(): View
    {
        // Stage B: paginated list of delegate's portal notifications
        abort(501);
    }

    public function markRead(int $id): RedirectResponse
    {
        // Stage B: mark single notification read, redirect back
        abort(501);
    }

    public function markAllRead(): RedirectResponse
    {
        // Stage B: mark all unread delegate notifications read
        abort(501);
    }

    public function countUnread(): JsonResponse
    {
        // Stage B: JSON {count: N} for badge polling
        abort(501);
    }
}
