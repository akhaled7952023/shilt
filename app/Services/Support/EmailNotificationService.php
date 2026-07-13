<?php

namespace App\Services\Support;

use App\Enums\NotificationCategory;
use App\Mail\AdminNotificationMail;
use App\Mail\DelegateNotificationMail;
use App\Models\Delegate;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Batch 7.3 — Simplified email delivery layer.
 *
 * Rules:
 * - Never throws — every method wraps in try/catch and logs on failure.
 * - Additive: does not replace or suppress portal notifications.
 * - Delegate emails go to delegates.email (no preference table needed).
 * - Admin emails go to the single notification_admin_email system setting.
 */
class EmailNotificationService
{
    /**
     * Send a notification email to a delegate by ID.
     * Skips silently if the delegate has no email address.
     */
    public function sendToDelegateIfEnabled(
        int $delegateId,
        NotificationCategory $category,
        string $title,
        string $body,
        ?string $actionUrl = null,
    ): void {
        try {
            $delegate = Delegate::find($delegateId);
            if (! $delegate || ! $delegate->email) {
                return;
            }

            Mail::to($delegate->email)->send(
                new DelegateNotificationMail($category, $title, $body, $actionUrl)
            );
        } catch (\Throwable $e) {
            Log::warning('EmailNotificationService: delegate email failed', [
                'delegate_id' => $delegateId,
                'category'    => $category->value,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send a notification email directly to a Delegate model instance.
     * Used by SendExpiryAlerts which already has the model.
     * Skips silently if no email address.
     */
    public function sendToDelegateModel(
        Delegate $delegate,
        NotificationCategory $category,
        string $title,
        string $body,
        ?string $actionUrl = null,
    ): void {
        if (! $delegate->email) {
            return;
        }

        try {
            Mail::to($delegate->email)->send(
                new DelegateNotificationMail($category, $title, $body, $actionUrl)
            );
        } catch (\Throwable $e) {
            Log::warning('EmailNotificationService: direct delegate email failed', [
                'delegate_id' => $delegate->id,
                'category'    => $category->value,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send an admin notification email to the single configured admin address.
     * Reads notification_admin_email from system settings.
     * Skips silently if the setting is empty or not configured.
     */
    public function sendToAdminSubscribers(
        NotificationCategory $category,
        string $title,
        string $body,
        ?string $actionUrl = null,
    ): void {
        try {
            $adminEmail = (string) (SystemSetting::get('notification_admin_email') ?? '');

            if (! $adminEmail || ! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                return;
            }

            Mail::to($adminEmail)->send(
                new AdminNotificationMail($category, $title, $body, $actionUrl)
            );
        } catch (\Throwable $e) {
            Log::warning('EmailNotificationService: admin email failed', [
                'category' => $category->value,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
