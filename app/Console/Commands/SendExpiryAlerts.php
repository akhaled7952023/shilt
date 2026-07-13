<?php

namespace App\Console\Commands;

use App\Enums\NotificationCategory;
use App\Models\Delegate;
use App\Models\SystemSetting;
use App\Services\Support\EmailNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * P4-010 — Daily expiry alerts sent to ALL admins for expiring delegate/vehicle documents.
 *
 * Setting key : notify_document_expiry_days (comma-separated, e.g. "30,14,7")
 * Recipient   : recipient_type = 'admin' (all admin users)
 * Dedup       : per (document, threshold) pair stored in data->threshold — never resends
 *               the same threshold-level alert for the same document.
 * Schedule    : daily at 08:00 via Laravel Scheduler — no manual execution needed.
 */
class SendExpiryAlerts extends Command
{
    protected $signature   = 'notify:expiry-alerts';
    protected $description = 'Send portal notifications to admins for expiring delegate/vehicle documents';

    public function __construct(private readonly EmailNotificationService $emailService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        // ── 1. Read and parse thresholds ──────────────────────────────────────
        $raw        = (string) (SystemSetting::get('notify_document_expiry_days') ?? '30');
        $thresholds = collect(explode(',', $raw))
            ->map(fn ($v) => (int) trim($v))
            ->filter(fn ($v) => $v > 0)
            ->sort()              // ascending: [7, 14, 30] — used to find most-urgent threshold
            ->values()
            ->toArray();

        if (empty($thresholds)) {
            $this->warn('No expiry thresholds configured (notify_document_expiry_days is empty).');
            return Command::SUCCESS;
        }

        $maxDays = max($thresholds);
        $today   = now()->toDateString();
        $maxDate = now()->addDays($maxDays)->toDateString();
        $debug   = config('app.debug', false);

        // ── 2. Load admin recipients ──────────────────────────────────────────
        $adminIds = DB::table('users')->whereNotNull('role_id')->pluck('id');
        if ($adminIds->isEmpty()) {
            $this->warn('No admin users found. Aborting.');
            return Command::SUCCESS;
        }

        $scanned = $matched = $alreadyNotified = $sent = $skippedInactive = 0;

        // ── 3. Delegate documents ─────────────────────────────────────────────
        // document_type_id: 1 = driving license, 2 = iqama
        $delegateDocs = DB::table('delegate_documents as dd')
            ->join('delegates as d', 'd.id', '=', 'dd.delegate_id')
            ->join('document_types as dt', 'dt.id', '=', 'dd.document_type_id')
            ->select(
                'dd.id',
                'dd.delegate_id',
                'dd.document_type_id',
                'dd.expiry_date',
                'd.name as delegate_name',
                'd.portal_enabled',
                'dt.name as doc_type_name',
            )
            ->whereNull('d.deleted_at')                          // exclude soft-deleted delegates
            ->whereIn('dd.document_type_id', [1, 2])
            ->whereDate('dd.expiry_date', '>=', $today)          // not already expired
            ->whereDate('dd.expiry_date', '<=', $maxDate)        // within widest window
            ->get();

        $scanned += $delegateDocs->count();

        foreach ($delegateDocs as $doc) {
            if (! $doc->portal_enabled) {
                $skippedInactive++;
                continue;
            }

            $category = match ((int) $doc->document_type_id) {
                1       => NotificationCategory::DrivingLicenseExpiring,
                2       => NotificationCategory::IqamaExpiring,
                default => null,
            };
            if (! $category) continue;

            $expiryDate = Carbon::parse($doc->expiry_date);
            $daysLeft   = (int) now()->startOfDay()->diffInDays($expiryDate->copy()->startOfDay(), false);

            // Find smallest (most urgent) qualifying threshold
            $activeThreshold = $this->resolveThreshold($daysLeft, $thresholds);
            if ($activeThreshold === null) continue;

            $matched++;

            if ($this->alreadySentForThreshold($category, 'delegate_document', $doc->id, $activeThreshold)) {
                $alreadyNotified++;
                continue;
            }

            $docName = $this->parseDocName($doc->doc_type_name);
            $this->fanOutToAdmins(
                adminIds:        $adminIds,
                category:        $category,
                title:           "انتهاء صلاحية {$docName} — {$doc->delegate_name}",
                body:            "ينتهي {$docName} للمندوب {$doc->delegate_name} خلال {$daysLeft} يوم (تاريخ الانتهاء: {$expiryDate->format('Y-m-d')}).",
                actionUrl:       route('dashboard.delegates.documents.index', $doc->delegate_id),
                notifiableType:  'delegate_document',
                notifiableId:    $doc->id,
                threshold:       $activeThreshold,
                delegateId:      $doc->delegate_id,
            );
            $sent++;
        }

        // ── 4. Vehicle documents ──────────────────────────────────────────────
        // document_type_id: 5 = vehicle registration, 6 = insurance
        $vehicleDocs = DB::table('vehicle_documents as vd')
            ->join('vehicle_assignments as va', function ($join) {
                $join->on('va.vehicle_id', '=', 'vd.vehicle_id')
                     ->where('va.is_active', true);
            })
            ->join('delegates as d', 'd.id', '=', 'va.delegate_id')
            ->join('document_types as dt', 'dt.id', '=', 'vd.document_type_id')
            ->select(
                'vd.id',
                'va.delegate_id',
                'vd.document_type_id',
                'vd.expiry_date',
                'd.name as delegate_name',
                'd.portal_enabled',
                'dt.name as doc_type_name',
            )
            ->whereNull('d.deleted_at')
            ->whereIn('vd.document_type_id', [5, 6])
            ->whereDate('vd.expiry_date', '>=', $today)
            ->whereDate('vd.expiry_date', '<=', $maxDate)
            ->get();

        $scanned += $vehicleDocs->count();

        foreach ($vehicleDocs as $doc) {
            if (! $doc->portal_enabled) {
                $skippedInactive++;
                continue;
            }

            $category = match ((int) $doc->document_type_id) {
                5       => NotificationCategory::VehicleRegistrationExpiring,
                6       => NotificationCategory::VehicleInsuranceExpiring,
                default => null,
            };
            if (! $category) continue;

            $expiryDate = Carbon::parse($doc->expiry_date);
            $daysLeft   = (int) now()->startOfDay()->diffInDays($expiryDate->copy()->startOfDay(), false);

            $activeThreshold = $this->resolveThreshold($daysLeft, $thresholds);
            if ($activeThreshold === null) continue;

            $matched++;

            if ($this->alreadySentForThreshold($category, 'vehicle_document', $doc->id, $activeThreshold)) {
                $alreadyNotified++;
                continue;
            }

            $docName = $this->parseDocName($doc->doc_type_name);
            $this->fanOutToAdmins(
                adminIds:        $adminIds,
                category:        $category,
                title:           "انتهاء صلاحية {$docName} (مركبة) — {$doc->delegate_name}",
                body:            "ينتهي {$docName} للمندوب {$doc->delegate_name} خلال {$daysLeft} يوم (تاريخ الانتهاء: {$expiryDate->format('Y-m-d')}).",
                actionUrl:       route('dashboard.delegates.documents.index', $doc->delegate_id),
                notifiableType:  'vehicle_document',
                notifiableId:    $doc->id,
                threshold:       $activeThreshold,
                delegateId:      $doc->delegate_id,
            );
            $sent++;
        }

        // ── 5. Output ─────────────────────────────────────────────────────────
        if ($debug) {
            $this->line('');
            $this->line("Documents scanned:   {$scanned}");
            $this->line("Matched threshold:   {$matched}");
            $this->line("Already notified:    {$alreadyNotified}");
            $this->line("Sent:                {$sent}");
            $this->line("Skipped inactive:    {$skippedInactive}");
            $this->line("Skipped expired:     0 (filtered at query level)");
            $this->line("Thresholds used:     " . implode(', ', array_reverse($thresholds)) . " days");
            $this->line("Admins notified:     " . $adminIds->count());
            $this->line('');
        }

        $this->info("Expiry alerts sent: {$sent}");
        return Command::SUCCESS;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Return the threshold that exactly matches daysLeft, or null.
     * Fires only on the configured day — not every day after crossing it.
     */
    private function resolveThreshold(int $daysLeft, array $thresholds): ?int
    {
        foreach ($thresholds as $T) {
            if ($daysLeft === $T) {
                return $T;
            }
        }
        return null;
    }

    /**
     * Dedup check: has this (document, threshold) pair ever been notified?
     * Uses JSON_EXTRACT on the data column so each threshold level sends exactly once.
     */
    private function alreadySentForThreshold(
        NotificationCategory $category,
        string $notifiableType,
        int $notifiableId,
        int $threshold,
    ): bool {
        return DB::table('notifications')
            ->where('category', $category->value)
            ->where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.threshold')) = ?", [(string) $threshold])
            ->exists();
    }

    /**
     * Bulk-insert one portal notification row per admin, then fan out emails.
     * Also sends a delegate-facing expiry reminder email if they have an address.
     */
    private function fanOutToAdmins(
        \Illuminate\Support\Collection $adminIds,
        NotificationCategory $category,
        string $title,
        string $body,
        string $actionUrl,
        string $notifiableType,
        int $notifiableId,
        int $threshold,
        int $delegateId,
    ): void {
        $now  = now()->toDateTimeString();
        $data = json_encode(['threshold' => $threshold, 'delegate_id' => $delegateId]);

        $rows = $adminIds->map(fn ($id) => [
            'recipient_type'  => 'admin',
            'recipient_id'    => $id,
            'channel'         => 'portal',
            'category'        => $category->value,
            'title'           => $title,
            'body'            => $body,
            'action_url'      => $actionUrl,
            'data'            => $data,
            'notifiable_type' => $notifiableType,
            'notifiable_id'   => $notifiableId,
            'sent_at'         => $now,
            'created_at'      => $now,
        ])->toArray();

        DB::table('notifications')->insert($rows);

        // Batch 7 — admin subscriber emails
        $this->emailService->sendToAdminSubscribers($category, $title, $body, $actionUrl);

        // Batch 7 — delegate expiry reminder email (portal notif goes to admin, email goes to delegate)
        $delegate = Delegate::find($delegateId);
        if ($delegate) {
            $this->emailService->sendToDelegateModel($delegate, $category, $title, $body, null);
        }
    }

    /**
     * Parse a document type name that may be stored as JSON {"ar":"...","en":"..."}.
     * Falls back to the raw string if not valid JSON.
     */
    private function parseDocName(string $raw): string
    {
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $locale = app()->getLocale();
            return $decoded[$locale] ?? $decoded['ar'] ?? $decoded['en'] ?? $raw;
        }
        return $raw;
    }
}
