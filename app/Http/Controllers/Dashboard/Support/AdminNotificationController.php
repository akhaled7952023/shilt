<?php

namespace App\Http\Controllers\Dashboard\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    // ── P7-006: Admin notification inbox (Batch 6) ────────────────────────────

    /**
     * Admin portal notification inbox — filterable by category group.
     *
     * ?filter=all|settlements|financial|support|documents|leaves
     */
    public function inbox(Request $request): View
    {
        $admin        = Auth::user();
        $activeFilter = $request->input('filter', 'all');

        $filterMap = [
            'settlements' => ['settlement_published', 'settlement_viewed'],
            'financial'   => ['financial_request_approved', 'financial_request_rejected'],
            'support'     => ['ticket_new', 'ticket_reply', 'ticket_closed', 'ticket_reopened'],
            'documents'   => ['iqama_expiring', 'passport_expiring', 'driving_license_expiring', 'vehicle_registration_expiring', 'vehicle_insurance_expiring'],
            'leaves'      => [],
        ];

        $query = SupportNotification::where('recipient_type', 'admin')
            ->where('recipient_id', $admin->id)
            ->where('channel', 'portal')
            ->orderByDesc('created_at');

        if ($activeFilter !== 'all' && isset($filterMap[$activeFilter])) {
            $cats = $filterMap[$activeFilter];
            if (empty($cats)) {
                $query->whereRaw('1 = 0');   // leaves tab — no categories defined yet
            } else {
                $query->whereIn('category', $cats);
            }
        }

        $notifications = $query->paginate(30)->withQueryString();

        // Mark ALL admin notifications read on any inbox visit (resets badge)
        try {
            DB::table('notifications')
                ->where('recipient_type', 'admin')
                ->where('recipient_id', $admin->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } catch (\Throwable) {}

        return view('dashboard.support.notifications.inbox', compact('notifications', 'activeFilter'));
    }

    /**
     * P4-002: JSON unread count for admin.
     */
    public function unreadCount(): JsonResponse
    {
        $count = 0;
        try {
            $count = DB::table('notifications')
                ->where('recipient_type', 'admin')
                ->where('recipient_id', Auth::id())
                ->where('channel', 'portal')
                ->whereNull('read_at')
                ->count();
        } catch (\Throwable) {}

        return response()->json(['count' => $count]);
    }

    /**
     * Mark a single admin notification as read.
     */
    public function markRead(Request $request, int $id): JsonResponse|RedirectResponse
    {
        try {
            DB::table('notifications')
                ->where('id', $id)
                ->where('recipient_type', 'admin')
                ->where('recipient_id', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } catch (\Throwable) {}

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    /**
     * Mark all unread admin notifications as read.
     */
    public function markAllRead(): RedirectResponse
    {
        try {
            DB::table('notifications')
                ->where('recipient_type', 'admin')
                ->where('recipient_id', Auth::id())
                ->where('channel', 'portal')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } catch (\Throwable) {}

        return redirect()->route('dashboard.support.notifications.inbox');
    }

}
