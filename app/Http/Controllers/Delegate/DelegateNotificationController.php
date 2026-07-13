<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use App\Models\DelegateNotification;
use App\Models\SupportNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DelegateNotificationController extends Controller
{
    public function index()
    {
        $delegate = Auth::guard('delegate')->user();

        // Mark all unread as read when the delegate opens the inbox
        DelegateNotification::where('delegate_id', $delegate->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        try {
            DB::table('notifications')
                ->where('recipient_type', 'delegate')
                ->where('recipient_id', $delegate->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } catch (\Throwable) {}

        // Merge both notification sources and sort by date descending
        $delegateNotifs = DelegateNotification::where('delegate_id', $delegate->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $supportNotifs = collect();
        try {
            $supportNotifs = SupportNotification::where('recipient_type', 'delegate')
                ->where('recipient_id', $delegate->id)
                ->where('channel', \App\Enums\NotificationChannel::Portal->value)
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Throwable) {}

        $all = $delegateNotifs->concat($supportNotifs)
            ->sortByDesc('created_at')
            ->values();

        $perPage = 25;
        $page    = (int) request()->get('page', 1);
        $notifications = new LengthAwarePaginator(
            $all->forPage($page, $perPage),
            $all->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('portal.notifications.index', compact('notifications'));
    }

    /**
     * P4-003: Mark a single notification as read.
     * Handles both DelegateNotification and SupportNotification by ID.
     * The route uses {id} (plain integer) — no implicit model binding.
     */
    public function markRead(Request $request, int $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $delegateId = Auth::guard('delegate')->id();

        // Try old DelegateNotification first
        $legacyRow = DelegateNotification::where('id', $id)
            ->where('delegate_id', $delegateId)
            ->first();

        if ($legacyRow) {
            $legacyRow->markAsRead();
        } else {
            // Fall back to Phase 3 notifications table
            try {
                DB::table('notifications')
                    ->where('id', $id)
                    ->where('recipient_type', 'delegate')
                    ->where('recipient_id', $delegateId)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            } catch (\Throwable) {}
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    /**
     * P4-003: Mark all unread notifications as read (both tables).
     */
    public function markAllRead(): \Illuminate\Http\RedirectResponse
    {
        $delegate = Auth::guard('delegate')->user();

        DelegateNotification::where('delegate_id', $delegate->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        try {
            DB::table('notifications')
                ->where('recipient_type', 'delegate')
                ->where('recipient_id', $delegate->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } catch (\Throwable) {}

        return redirect()->route('portal.notifications.index')
            ->with('success', __('portal.flash_all_read'));
    }

    /**
     * P4-002: JSON endpoint — returns total unread count across both tables.
     */
    public function unreadCount(): JsonResponse
    {
        $delegate = Auth::guard('delegate')->user();

        $legacy = DelegateNotification::where('delegate_id', $delegate->id)
            ->whereNull('read_at')
            ->count();

        $phase3 = 0;
        try {
            $phase3 = DB::table('notifications')
                ->where('recipient_type', 'delegate')
                ->where('recipient_id', $delegate->id)
                ->where('channel', \App\Enums\NotificationChannel::Portal->value)
                ->whereNull('read_at')
                ->count();
        } catch (\Throwable) {}

        return response()->json(['count' => $legacy + $phase3]);
    }
}
