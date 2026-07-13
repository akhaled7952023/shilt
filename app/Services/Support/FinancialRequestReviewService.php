<?php

namespace App\Services\Support;

use App\Enums\FinancialRequestStatus;
use App\Enums\NotificationCategory;
use App\Enums\NotificationChannel;
use App\Enums\PendingEntryStatus;
use App\Enums\TicketStatus;
use App\Models\FinancialRequest;
use App\Models\HungerStationFtrDelegateDeduction;
use App\Models\PendingFinancialEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Phase 3 — Batch 4 — Financial request review workflow.
 *
 * Handles Approve, Reject, and Cancel actions.
 * Every operation runs inside a DB::transaction.
 * Business rule (Issues 4+5): public reply is stored BEFORE the status changes.
 * Notification/activity failures are logged and never roll back the business transaction.
 */
class FinancialRequestReviewService
{
    public function __construct(
        private readonly TicketLifecycleService  $lifecycleService,
        private readonly TicketAuditLogger       $auditLogger,
        private readonly AdminActivityLogger     $activityLogger,
        private readonly TicketReplyService      $replyService,
        private readonly EmailNotificationService $emailService,
    ) {}

    /**
     * Approve a financial request.
     * Creates a PendingFinancialEntry and resolves the ticket.
     *
     * @param  FinancialRequest $financialRequest  Must have 'ticket' relationship loaded.
     * @param  array            $data {
     *   approved_amount:  float,
     *   deduction_type:   string,
     *   is_benefit:       bool,
     *   settlement_month: int (1–12),
     *   settlement_year:  int (e.g. 2026),
     *   approved_label:   string|null,
     *   notes:            string|null,
     *   delegate_message: string|null,
     * }
     * @param  User             $admin
     * @return PendingFinancialEntry
     * @throws \RuntimeException  'already_reviewed' | 'cannot_reapprove'
     */
    public function approve(FinancialRequest $financialRequest, array $data, User $admin): PendingFinancialEntry
    {
        $entry = DB::transaction(function () use ($financialRequest, $data, $admin) {
            // Lock the row to prevent concurrent double-approval
            $financialRequest = FinancialRequest::lockForUpdate()->findOrFail($financialRequest->id);

            if (! $financialRequest->canBeApproved()) {
                throw new \RuntimeException('already_reviewed');
            }

            $ticket = $financialRequest->ticket;

            // ── Issue 4+5: Public reply BEFORE status change ──────────────────
            if (! empty($data['delegate_message'])) {
                $this->replyService->postAdminReply(
                    ticket: $ticket,
                    content: $data['delegate_message'],
                    isInternalNote: false,
                    admin: $admin,
                    files: [],
                );
            }

            // Derive label from type constants if not explicitly provided
            $allLabels = array_merge(
                HungerStationFtrDelegateDeduction::DEDUCTION_TYPE_LABELS,
                HungerStationFtrDelegateDeduction::BENEFIT_TYPE_LABELS,
            );
            $label = (! empty($data['approved_label']))
                ? $data['approved_label']
                : ($allLabels[$data['deduction_type']] ?? $data['deduction_type']);

            $settlementMonth = (int) $data['settlement_month'];
            $settlementYear  = (int) $data['settlement_year'];

            // Cancel any previously-existing entry (only reachable when canBeApproved allows re-approval)
            $existing = $financialRequest->pendingEntry;
            if ($existing && $existing->isCancelled()) {
                $existing->status           = PendingEntryStatus::Pending;
                $existing->amount           = $data['approved_amount'];
                $existing->deduction_type   = $data['deduction_type'];
                $existing->is_benefit       = (bool) $data['is_benefit'];
                $existing->label            = $label;
                $existing->notes            = $data['notes'] ?? null;
                $existing->monthly_period_id = null;
                $existing->settlement_month = $settlementMonth;
                $existing->settlement_year  = $settlementYear;
                $existing->created_by       = $admin->id;
                $existing->imported_at      = null;
                $existing->imported_by      = null;
                $existing->settlement_id    = null;
                $existing->adjustment_id    = null;
                $existing->save();
                $entry = $existing;
            } else {
                $entry = PendingFinancialEntry::create([
                    'platform'             => 'hungerstation',
                    'source_type'          => 'ticket',
                    'financial_request_id' => $financialRequest->id,
                    'delegate_id'          => $financialRequest->delegate_id,
                    'monthly_period_id'    => null,
                    'settlement_month'     => $settlementMonth,
                    'settlement_year'      => $settlementYear,
                    'deduction_type'       => $data['deduction_type'],
                    'is_benefit'           => (bool) $data['is_benefit'],
                    'label'                => $label,
                    'amount'               => $data['approved_amount'],
                    'notes'                => $data['notes'] ?? null,
                    'status'               => PendingEntryStatus::Pending,
                    'created_by'           => $admin->id,
                ]);
            }

            // Update the financial request
            $financialRequest->status                    = FinancialRequestStatus::Approved;
            $financialRequest->reviewed_by               = $admin->id;
            $financialRequest->reviewed_at               = now();
            $financialRequest->approved_deduction_type   = $data['deduction_type'];
            $financialRequest->approved_is_benefit       = (bool) $data['is_benefit'];
            $financialRequest->approved_label            = $label;
            $financialRequest->approved_amount           = $data['approved_amount'];
            $financialRequest->approved_monthly_period_id = null;
            $financialRequest->settlement_month          = $settlementMonth;
            $financialRequest->settlement_year           = $settlementYear;
            $financialRequest->approved_notes            = $data['notes'] ?? null;
            $financialRequest->save();

            // ── Status changes AFTER reply (Issues 4+5) ───────────────────────
            $this->lifecycleService->resolve($ticket, $admin);

            // Audit log — synchronous write inside the transaction
            $this->auditLogger->logAdmin(
                ticket: $ticket,
                admin: $admin,
                action: 'financial_request_approved',
                description: "Financial request approved. Amount: {$data['approved_amount']} SAR. "
                    . "Settlement: {$settlementMonth}/{$settlementYear}. Pending entry #{$entry->id} created.",
                fromValue: $financialRequest->getRawOriginal('status') ?? FinancialRequestStatus::Pending->value,
                toValue: FinancialRequestStatus::Approved->value,
            );

            // Unconditional delegate notification for approval
            try {
                if ($financialRequest->delegate_id) {
                    DB::table('notifications')->insert([
                        'recipient_type'  => 'delegate',
                        'recipient_id'    => $financialRequest->delegate_id,
                        'channel'         => NotificationChannel::Portal->value,
                        'category'        => NotificationCategory::FinancialRequestApproved->value,
                        'title'           => "تمت الموافقة على طلبك المالي للتذكرة {$ticket->ticket_number}",
                        'body'            => "المبلغ المعتمد: {$data['approved_amount']} ريال — سيُضاف إلى تسوية {$settlementMonth}/{$settlementYear}.",
                        'action_url'      => route('portal.support.tickets.show', $ticket),
                        'notifiable_type' => \App\Models\SupportTicket::class,
                        'notifiable_id'   => $ticket->id,
                        'sent_at'         => now()->toDateTimeString(),
                        'created_at'      => now()->toDateTimeString(),
                    ]);
                }
            } catch (\Throwable) {}

            return $entry;
        });

        try {
            if ($financialRequest->delegate_id) {
                $ticketNum = $financialRequest->ticket?->ticket_number ?? "#{$financialRequest->ticket_id}";
                $month     = (int) $data['settlement_month'];
                $year      = (int) $data['settlement_year'];
                $actionUrl = $financialRequest->ticket
                    ? route('portal.support.tickets.show', $financialRequest->ticket)
                    : null;
                $this->emailService->sendToDelegateIfEnabled(
                    $financialRequest->delegate_id,
                    NotificationCategory::FinancialRequestApproved,
                    "تمت الموافقة على طلبك المالي للتذكرة {$ticketNum}",
                    "المبلغ المعتمد: {$data['approved_amount']} ريال — سيُضاف إلى تسوية {$month}/{$year}.",
                    $actionUrl,
                );
            }
        } catch (\Throwable $e) {
            Log::warning('FinancialRequestReviewService: email failed after approve', ['error' => $e->getMessage()]);
        }

        return $entry;
    }

    /**
     * Reject a financial request and resolve the ticket.
     * The rejection_reason is automatically posted as a public reply before the status changes.
     *
     * @throws \RuntimeException  'already_reviewed'
     */
    public function reject(FinancialRequest $financialRequest, string $reason, User $admin): void
    {
        DB::transaction(function () use ($financialRequest, $reason, $admin) {
            $financialRequest = FinancialRequest::lockForUpdate()->findOrFail($financialRequest->id);

            if (! $financialRequest->canBeRejected()) {
                throw new \RuntimeException('already_reviewed');
            }

            $ticket = $financialRequest->ticket;

            // ── Issue 4+5: Post rejection reason as public reply BEFORE status change ──
            $this->replyService->postAdminReply(
                ticket: $ticket,
                content: $reason,
                isInternalNote: false,
                admin: $admin,
                files: [],
            );

            // ── Status changes AFTER reply ─────────────────────────────────────
            $financialRequest->status           = FinancialRequestStatus::Rejected;
            $financialRequest->reviewed_by      = $admin->id;
            $financialRequest->reviewed_at      = now();
            $financialRequest->rejection_reason = $reason;
            $financialRequest->save();

            $this->lifecycleService->resolve($ticket, $admin);

            $this->auditLogger->logAdmin(
                ticket: $ticket,
                admin: $admin,
                action: 'financial_request_rejected',
                description: "Financial request rejected. Reason: {$reason}",
                fromValue: FinancialRequestStatus::Pending->value,
                toValue: FinancialRequestStatus::Rejected->value,
            );
        });

        // P4-007: notify the delegate about the rejection
        try {
            if ($financialRequest->delegate_id) {
                $ticketNum = $financialRequest->ticket?->ticket_number;
                $actionUrl = $financialRequest->ticket
                    ? route('portal.support.tickets.show', $financialRequest->ticket)
                    : null;
                DB::table('notifications')->insert([
                    'recipient_type'  => 'delegate',
                    'recipient_id'    => $financialRequest->delegate_id,
                    'channel'         => NotificationChannel::Portal->value,
                    'category'        => NotificationCategory::FinancialRequestRejected->value,
                    'title'           => "تم رفض طلبك المالي للتذكرة {$ticketNum}",
                    'body'            => "سبب الرفض: {$reason}",
                    'action_url'      => $actionUrl,
                    'notifiable_type' => \App\Models\SupportTicket::class,
                    'notifiable_id'   => $financialRequest->ticket_id,
                    'sent_at'         => now()->toDateTimeString(),
                    'created_at'      => now()->toDateTimeString(),
                ]);
                $this->emailService->sendToDelegateIfEnabled(
                    $financialRequest->delegate_id,
                    NotificationCategory::FinancialRequestRejected,
                    "تم رفض طلبك المالي للتذكرة {$ticketNum}",
                    "سبب الرفض: {$reason}",
                    $actionUrl,
                );
            }
        } catch (\Throwable $e) {
            Log::warning('FinancialRequestReviewService: notification failed after reject', ['error' => $e->getMessage()]);
        }

        try {
            $this->activityLogger->logAdmin(
                admin: $admin,
                action: 'request_rejected',
                description: "Rejected financial request for ticket #{$financialRequest->ticket_id}.",
                subject: $financialRequest,
                subjectLabel: $financialRequest->ticket?->ticket_number,
            );
        } catch (\Throwable $e) {
            Log::warning('FinancialRequestReviewService: activity log failed after reject', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel a pending financial entry (super_admin only — enforced at controller level).
     * Resets the parent FinancialRequest to Pending so it can be re-approved.
     *
     * @throws \RuntimeException  'cannot_cancel'
     */
    public function cancel(PendingFinancialEntry $entry, string $reason, User $admin): void
    {
        DB::transaction(function () use ($entry, $reason, $admin) {
            $entry = PendingFinancialEntry::lockForUpdate()->findOrFail($entry->id);

            if (! $entry->canBeCancelled()) {
                throw new \RuntimeException('cannot_cancel');
            }

            $entry->status = PendingEntryStatus::Cancelled;
            $entry->notes  = trim(($entry->notes ? $entry->notes . "\n" : '') . "Cancelled: {$reason}");
            $entry->save();

            // Reset the financial request so the admin can re-approve if needed
            $fr = $entry->financialRequest;
            if ($fr) {
                $fr->status                    = FinancialRequestStatus::Pending;
                $fr->reviewed_by               = null;
                $fr->reviewed_at               = null;
                $fr->approved_amount           = null;
                $fr->approved_deduction_type   = null;
                $fr->approved_is_benefit       = null;
                $fr->approved_label            = null;
                $fr->approved_monthly_period_id = null;
                $fr->settlement_month          = null;
                $fr->settlement_year           = null;
                $fr->approved_notes            = null;
                $fr->save();

                $ticket = $fr->ticket;
                if ($ticket) {
                    $this->auditLogger->logAdmin(
                        ticket: $ticket,
                        admin: $admin,
                        action: 'financial_entry_cancelled',
                        description: "Pending financial entry #{$entry->id} cancelled. Reason: {$reason}. Financial request reset to pending.",
                        fromValue: PendingEntryStatus::Pending->value,
                        toValue: PendingEntryStatus::Cancelled->value,
                    );
                }
            }
        });

        try {
            $this->activityLogger->logAdmin(
                admin: $admin,
                action: 'pending_entry_cancelled',
                description: "Cancelled pending financial entry #{$entry->id}.",
            );
        } catch (\Throwable $e) {
            Log::warning('FinancialRequestReviewService: activity log failed after cancel', ['error' => $e->getMessage()]);
        }
    }
}
