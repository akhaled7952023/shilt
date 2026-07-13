<?php

use App\Enums\NotificationCategory;
use App\Mail\DelegateNotificationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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

// ── Batch 7: Debug mail preview (local / APP_DEBUG=true only) ──────────────
// Access: /debug/mail/preview?to=you@example.com
// Remove or gate this route before production use.
if (config('app.debug')) {
    Route::get('/debug/mail/preview', function () {
        $category = NotificationCategory::from(
            request('category', NotificationCategory::SettlementPublished->value)
        );
        return new DelegateNotificationMail(
            category:  $category,
            notifTitle: 'معاينة: ' . $category->value,
            notifBody:  'هذه رسالة معاينة لقالب البريد الإلكتروني. يمكنك تغيير الفئة عبر ?category=ticket_reply',
            actionUrl:  config('app.url'),
        );
    })->name('debug.mail.preview');

    Route::get('/debug/mail/send', function () {
        $to = request('to');
        if (! $to || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return response('Please provide ?to=email@example.com', 400);
        }

        $category = NotificationCategory::from(
            request('category', NotificationCategory::SettlementPublished->value)
        );

        Mail::to($to)->send(new DelegateNotificationMail(
            category:  $category,
            notifTitle: 'اختبار: ' . $category->value,
            notifBody:  'رسالة اختبارية من نظام منادب — ' . now()->toDateTimeString(),
            actionUrl:  config('app.url'),
        ));

        return response("✓ Email sent to {$to}");
    })->name('debug.mail.send');
}
