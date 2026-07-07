<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use App\Models\DelegateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DelegateNotificationController extends Controller
{
    public function index()
    {
        $delegate = Auth::guard('delegate')->user();

        // Mark all unread as read the moment the delegate opens the page
        DelegateNotification::where('delegate_id', $delegate->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $notifications = DelegateNotification::where('delegate_id', $delegate->id)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('portal.notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, DelegateNotification $notification)
    {
        abort_if($notification->delegate_id !== Auth::guard('delegate')->id(), 403);

        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    public function markAllRead()
    {
        $delegate = Auth::guard('delegate')->user();

        DelegateNotification::where('delegate_id', $delegate->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->route('portal.notifications.index')
            ->with('success', __('portal.flash_all_read'));
    }
}
