<?php

namespace App\Services\Support;

use App\Enums\NotificationCategory;
use App\Enums\NotificationChannel;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Phase 3 — Orchestrates all SupportTicket status transitions.
 *
 * Single responsibility: lifecycle state changes.
 * No HTTP, no validation, no views — callers handle those.
 * Every mutation is wrapped in a DB transaction.
 */
class TicketLifecycleService
{
    public function __construct(
        private readonly SlaCalculator     $slaCalculator,
        private readonly TicketAuditLogger $auditLogger,
        private readonly AdminActivityLogger $activityLogger,
    ) {}

    /**
     * Change a ticket's priority and recalculate SLA deadlines.
     * Caller must ensure the ticket is not permanently closed.
     */
    public function changePriority(SupportTicket $ticket, TicketPriority $newPriority, User $admin): void
    {
        DB::transaction(function () use ($ticket, $newPriority, $admin) {
            $oldPriority   = $ticket->priority;
            $ticket->priority = $newPriority;
            $this->slaCalculator->recalculate($ticket);
            $ticket->updated_by = $admin->id;
            $ticket->save();

            $this->auditLogger->logAdmin(
                ticket: $ticket,
                admin: $admin,
                action: 'priority_changed',
                description: "Priority changed from {$oldPriority->value} to {$newPriority->value}.",
                fromValue: $oldPriority->value,
                toValue: $newPriority->value,
            );

            try {
                $this->activityLogger->logAdmin(
                    admin: $admin,
                    action: 'ticket_priority_changed',
                    description: "Changed priority of ticket {$ticket->ticket_number} from {$oldPriority->label()} to {$newPriority->label()}.",
                    subject: $ticket,
                    subjectLabel: $ticket->ticket_number,
                );
            } catch (\Throwable) {}
        });
    }

    /**
     * Mark a ticket as resolved and start the grace period countdown.
     */
    public function resolve(SupportTicket $ticket, User $admin): void
    {
        DB::transaction(function () use ($ticket, $admin) {
            $ticket->markResolved($admin);
            $ticket->save();

            $this->auditLogger->logAdmin(
                ticket: $ticket,
                admin: $admin,
                action: 'resolved',
                description: 'Ticket marked as resolved. Grace period started.',
                fromValue: TicketStatus::InProgress->value,
                toValue: TicketStatus::Resolved->value,
            );

            try {
                $this->activityLogger->logAdmin(
                    admin: $admin,
                    action: 'ticket_resolved',
                    description: "Resolved ticket {$ticket->ticket_number}.",
                    subject: $ticket,
                    subjectLabel: $ticket->ticket_number,
                );
            } catch (\Throwable) {}

            try {
                if ($ticket->delegate_id) {
                    DB::table('notifications')->insert([
                        'recipient_type'  => 'delegate',
                        'recipient_id'    => $ticket->delegate_id,
                        'channel'         => NotificationChannel::Portal->value,
                        'category'        => NotificationCategory::TicketClosed->value,
                        'title'           => "تم حل تذكرتك {$ticket->ticket_number}",
                        'body'            => 'تم حل تذكرتك. يمكنك إعادة فتحها خلال فترة الانتظار إذا لم يُحلّ مشكلتك.',
                        'action_url'      => route('portal.support.tickets.show', $ticket),
                        'notifiable_type' => SupportTicket::class,
                        'notifiable_id'   => $ticket->id,
                        'sent_at'         => now()->toDateTimeString(),
                        'created_at'      => now()->toDateTimeString(),
                    ]);
                }
            } catch (\Throwable) {}
        });
    }

    /**
     * Permanently close a ticket (admin force-close, bypasses grace period).
     * Restricted to super_admin — caller must gate this.
     */
    public function forceClose(SupportTicket $ticket, User $admin): void
    {
        DB::transaction(function () use ($ticket, $admin) {
            $ticket->markClosed();
            $ticket->updated_by = $admin->id;
            $ticket->save();

            $this->auditLogger->logAdmin(
                ticket: $ticket,
                admin: $admin,
                action: 'closed',
                description: 'Force closed by admin.',
                fromValue: $ticket->getRawOriginal('status'),
                toValue: TicketStatus::Closed->value,
            );

            try {
                $this->activityLogger->logAdmin(
                    admin: $admin,
                    action: 'ticket_force_closed',
                    description: "Force closed ticket {$ticket->ticket_number}.",
                    subject: $ticket,
                    subjectLabel: $ticket->ticket_number,
                );
            } catch (\Throwable) {}

            try {
                if ($ticket->delegate_id) {
                    DB::table('notifications')->insert([
                        'recipient_type'  => 'delegate',
                        'recipient_id'    => $ticket->delegate_id,
                        'channel'         => NotificationChannel::Portal->value,
                        'category'        => NotificationCategory::TicketClosed->value,
                        'title'           => "تم إغلاق تذكرتك {$ticket->ticket_number}",
                        'body'            => 'تم إغلاق تذكرتك نهائياً من قبل المسؤول.',
                        'action_url'      => route('portal.support.tickets.show', $ticket),
                        'notifiable_type' => SupportTicket::class,
                        'notifiable_id'   => $ticket->id,
                        'sent_at'         => now()->toDateTimeString(),
                        'created_at'      => now()->toDateTimeString(),
                    ]);
                }
            } catch (\Throwable) {}
        });
    }

    /**
     * Expire a resolved ticket after its grace period (called by the scheduler command).
     * No admin actor — this is a system event.
     */
    public function expireGracePeriod(SupportTicket $ticket): void
    {
        DB::transaction(function () use ($ticket) {
            $ticket->markClosed();
            $ticket->save();

            $this->auditLogger->logSystem(
                ticket: $ticket,
                action: 'closed',
                description: 'Automatically closed after grace period expired.',
                fromValue: TicketStatus::Resolved->value,
                toValue: TicketStatus::Closed->value,
            );

            try {
                $this->activityLogger->logSystem(
                    action: 'ticket_grace_expired',
                    description: "Ticket {$ticket->ticket_number} automatically closed after grace period.",
                    subject: $ticket,
                    subjectLabel: $ticket->ticket_number,
                );
            } catch (\Throwable) {}
        });
    }

    /**
     * Reopen a closed ticket (admin-initiated).
     */
    public function reopen(SupportTicket $ticket, User $admin): void
    {
        DB::transaction(function () use ($ticket, $admin) {
            $previousStatus = $ticket->status;

            $ticket->status               = TicketStatus::Reopened;
            $ticket->permanently_closed_at = null;
            $ticket->closed_at            = null;
            $ticket->last_activity_at     = now();
            $ticket->updated_by           = $admin->id;
            $ticket->save();

            $this->auditLogger->logAdmin(
                ticket: $ticket,
                admin: $admin,
                action: 'reopened',
                description: 'Ticket reopened.',
                fromValue: $previousStatus->value,
                toValue: TicketStatus::Reopened->value,
            );

            try {
                $this->activityLogger->logAdmin(
                    admin: $admin,
                    action: 'ticket_reopened',
                    description: "Reopened ticket {$ticket->ticket_number}.",
                    subject: $ticket,
                    subjectLabel: $ticket->ticket_number,
                );
            } catch (\Throwable) {}
        });
    }
}
