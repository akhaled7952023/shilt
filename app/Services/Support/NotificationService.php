<?php

namespace App\Services\Support;

use App\Enums\NotificationCategory;
use App\Services\Support\EmailNotificationService;
use App\Enums\NotificationChannel;
use App\Models\SupportNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Phase 3 — Unified portal notification dispatcher.
 *
 * Handles portal channel only; email is dispatched additively via EmailNotificationService.
 * All writes go directly to the `notifications` table — no queuing.
 * Non-blocking by design: every public method is wrapped in try/catch at call sites.
 */
class NotificationService
{
    public function __construct(
        private readonly EmailNotificationService $emailService,
    ) {}
    /**
     * Insert one portal notification row for a single recipient.
     * Safe to call inside or outside DB transactions.
     */
    public function sendPortalNotification(
        string $recipientType,
        int $recipientId,
        NotificationCategory $category,
        string $title,
        string $body,
        ?string $actionUrl = null,
        array $data = [],
        ?string $notifiableType = null,
        ?int $notifiableId = null,
    ): SupportNotification {
        $now = now()->toDateTimeString();

        $id = DB::table('notifications')->insertGetId([
            'recipient_type'  => $recipientType,
            'recipient_id'    => $recipientId,
            'channel'         => NotificationChannel::Portal->value,
            'category'        => $category->value,
            'title'           => $title,
            'body'            => $body,
            'action_url'      => $actionUrl,
            'data'            => $data ? json_encode($data) : null,
            'notifiable_type' => $notifiableType,
            'notifiable_id'   => $notifiableId,
            'sent_at'         => $now,
            'created_at'      => $now,
        ]);

        $notif          = new SupportNotification($this->rowArray($recipientType, $recipientId, $category, $title, $body, $actionUrl, $data, $notifiableType, $notifiableId));
        $notif->id      = $id;
        $notif->sent_at = now();
        $notif->created_at = now();
        $notif->exists  = true;

        // Batch 7 — additive email channel
        if ($recipientType === 'delegate') {
            $this->emailService->sendToDelegateIfEnabled($recipientId, $category, $title, $body, $actionUrl);
        }

        return $notif;
    }

    /**
     * Fan-out: insert one portal notification row for every active admin user.
     * Uses a single bulk INSERT.
     */
    public function sendToAllAdmins(
        NotificationCategory $category,
        string $title,
        string $body,
        ?string $actionUrl = null,
        array $data = [],
        ?string $notifiableType = null,
        ?int $notifiableId = null,
    ): int {
        $adminIds = User::whereNotNull('role_id')->pluck('id');

        if ($adminIds->isEmpty()) {
            return 0;
        }

        $now  = now()->toDateTimeString();
        $rows = $adminIds->map(fn ($id) => [
            'recipient_type'  => 'admin',
            'recipient_id'    => $id,
            'channel'         => NotificationChannel::Portal->value,
            'category'        => $category->value,
            'title'           => $title,
            'body'            => $body,
            'action_url'      => $actionUrl,
            'data'            => $data ? json_encode($data) : null,
            'notifiable_type' => $notifiableType,
            'notifiable_id'   => $notifiableId,
            'sent_at'         => $now,
            'created_at'      => $now,
        ])->toArray();

        DB::table('notifications')->insert($rows);

        // Batch 7 — email to admin subscribers
        $this->emailService->sendToAdminSubscribers($category, $title, $body, $actionUrl);

        return count($rows);
    }

    /**
     * Fan-out: insert one portal notification for admins who have a specific permission.
     */
    public function sendToSupportAdmins(
        NotificationCategory $category,
        string $title,
        string $body,
        ?string $actionUrl = null,
        array $data = [],
        ?string $notifiableType = null,
        ?int $notifiableId = null,
    ): int {
        $adminIds = User::whereNotNull('role_id')
            ->whereHas('role', fn ($q) => $q->where('permissions', 'like', '%"support"%'))
            ->pluck('id');

        if ($adminIds->isEmpty()) {
            return 0;
        }

        $now  = now()->toDateTimeString();
        $rows = $adminIds->map(fn ($id) => [
            'recipient_type'  => 'admin',
            'recipient_id'    => $id,
            'channel'         => NotificationChannel::Portal->value,
            'category'        => $category->value,
            'title'           => $title,
            'body'            => $body,
            'action_url'      => $actionUrl,
            'data'            => $data ? json_encode($data) : null,
            'notifiable_type' => $notifiableType,
            'notifiable_id'   => $notifiableId,
            'sent_at'         => $now,
            'created_at'      => $now,
        ])->toArray();

        DB::table('notifications')->insert($rows);

        // Batch 7 — email to admin subscribers
        $this->emailService->sendToAdminSubscribers($category, $title, $body, $actionUrl);

        return count($rows);
    }

    /**
     * Mark a single portal notification as read.
     * Returns true if the row was updated.
     */
    public function markRead(int $notificationId, string $recipientType, int $recipientId): bool
    {
        return DB::table('notifications')
            ->where('id', $notificationId)
            ->where('recipient_type', $recipientType)
            ->where('recipient_id', $recipientId)
            ->whereNull('read_at')
            ->update(['read_at' => now()->toDateTimeString()]) > 0;
    }

    /**
     * Mark all unread portal notifications for a recipient as read.
     * Returns the number of rows updated.
     */
    public function markAllRead(string $recipientType, int $recipientId): int
    {
        return DB::table('notifications')
            ->where('recipient_type', $recipientType)
            ->where('recipient_id', $recipientId)
            ->where('channel', NotificationChannel::Portal->value)
            ->whereNull('read_at')
            ->update(['read_at' => now()->toDateTimeString()]);
    }

    /**
     * Count unread portal notifications for a recipient.
     */
    public function countUnread(string $recipientType, int $recipientId): int
    {
        return DB::table('notifications')
            ->where('recipient_type', $recipientType)
            ->where('recipient_id', $recipientId)
            ->where('channel', NotificationChannel::Portal->value)
            ->whereNull('read_at')
            ->count();
    }

    // ── Internal helper ───────────────────────────────────────────────────────

    private function rowArray(
        string $recipientType, int $recipientId,
        NotificationCategory $category,
        string $title, string $body,
        ?string $actionUrl, array $data,
        ?string $notifiableType, ?int $notifiableId,
    ): array {
        return [
            'recipient_type'  => $recipientType,
            'recipient_id'    => $recipientId,
            'channel'         => NotificationChannel::Portal->value,
            'category'        => $category->value,
            'title'           => $title,
            'body'            => $body,
            'action_url'      => $actionUrl,
            'data'            => $data ?: null,
            'notifiable_type' => $notifiableType,
            'notifiable_id'   => $notifiableId,
        ];
    }
}
