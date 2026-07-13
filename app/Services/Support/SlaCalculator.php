<?php

namespace App\Services\Support;

use App\Enums\TicketPriority;
use App\Models\SlaPolicy;
use App\Models\SupportTicket;
use Carbon\Carbon;

/**
 * Phase 3 — SLA deadline calculator.
 *
 * Computes first-response and resolution deadlines from a ticket's opened_at
 * timestamp and the active SlaPolicy for its priority.
 *
 * All methods that operate on a SupportTicket are pure model mutations:
 * they modify the model in memory but do NOT save to the database.
 * The caller is responsible for calling $ticket->save() when ready.
 */
class SlaCalculator
{
    public function __construct(private readonly SlaPolicy $slaPolicyModel) {}

    // ── Ticket-level mutations (called by TicketService in Stage B) ───────────

    /**
     * Set sla_first_response_deadline and sla_resolution_deadline on the ticket.
     * Does NOT save the model — caller saves.
     */
    public function applyToTicket(SupportTicket $ticket): void
    {
        $policy = SlaPolicy::forPriority($ticket->priority);
        if (! $policy || ! $ticket->opened_at) {
            return;
        }

        $ticket->sla_first_response_deadline = $ticket->opened_at->copy()
            ->addHours($policy->first_response_hours);

        $ticket->sla_resolution_deadline = $ticket->opened_at->copy()
            ->addHours($policy->resolution_hours);
    }

    /**
     * Evaluate and set sla_first_response_met based on first_reply_at vs deadline.
     * 1 = met on time, 0 = missed, null = no reply yet.
     * Does NOT save the model — caller saves.
     */
    public function evaluateFirstResponse(SupportTicket $ticket): void
    {
        if (! $ticket->sla_first_response_deadline) {
            return;
        }

        if (! $ticket->first_reply_at) {
            $ticket->sla_first_response_met = null;
            return;
        }

        $ticket->sla_first_response_met = $ticket->first_reply_at->lte($ticket->sla_first_response_deadline)
            ? 1
            : 0;
    }

    /**
     * Evaluate and set sla_resolution_met based on resolved_at vs deadline.
     * 1 = met on time, 0 = missed, null = not resolved yet.
     * Does NOT save the model — caller saves.
     */
    public function evaluateResolution(SupportTicket $ticket): void
    {
        if (! $ticket->sla_resolution_deadline) {
            return;
        }

        if (! $ticket->resolved_at) {
            $ticket->sla_resolution_met = null;
            return;
        }

        $ticket->sla_resolution_met = $ticket->resolved_at->lte($ticket->sla_resolution_deadline)
            ? 1
            : 0;
    }

    /**
     * Recalculate SLA deadlines (e.g. after a priority change) then re-evaluate
     * any outcome fields whose anchor timestamps already exist.
     * Does NOT save the model — caller saves.
     */
    public function recalculate(SupportTicket $ticket): void
    {
        $this->applyToTicket($ticket);
        $this->evaluateFirstResponse($ticket);
        $this->evaluateResolution($ticket);
    }

    // ── Pure helpers (no model dependency) ───────────────────────────────────

    /**
     * Calculate the first-response deadline for a given priority and open time.
     *
     * @return Carbon|null  Null if no SLA policy is configured for this priority
     */
    public function firstResponseDeadline(TicketPriority $priority, Carbon $openedAt): ?Carbon
    {
        $policy = SlaPolicy::forPriority($priority);
        if (! $policy) {
            return null;
        }

        return $openedAt->copy()->addHours($policy->first_response_hours);
    }

    /**
     * Calculate the resolution deadline for a given priority and open time.
     *
     * @return Carbon|null  Null if no SLA policy is configured for this priority
     */
    public function resolutionDeadline(TicketPriority $priority, Carbon $openedAt): ?Carbon
    {
        $policy = SlaPolicy::forPriority($priority);
        if (! $policy) {
            return null;
        }

        return $openedAt->copy()->addHours($policy->resolution_hours);
    }

    /** Calculate the grace-period deadline (deadline + grace hours). */
    public function graceDeadline(Carbon $deadline, int $graceHours): Carbon
    {
        return $deadline->copy()->addHours($graceHours);
    }

    /** True if the first-response deadline has passed and no reply has been sent. */
    public function isFirstResponseBreached(SupportTicket $ticket, ?Carbon $asOf = null): bool
    {
        if (! $ticket->sla_first_response_deadline || $ticket->first_reply_at) {
            return false;
        }

        return ($asOf ?? now())->gt($ticket->sla_first_response_deadline);
    }

    /** True if the resolution deadline has passed and the ticket is not resolved. */
    public function isResolutionBreached(SupportTicket $ticket, ?Carbon $asOf = null): bool
    {
        if (! $ticket->sla_resolution_deadline || $ticket->resolved_at) {
            return false;
        }

        return ($asOf ?? now())->gt($ticket->sla_resolution_deadline);
    }

    /** Default grace hours per priority (used at ticket creation if no system_setting override). */
    public function defaultGraceHours(TicketPriority $priority): int
    {
        return match ($priority) {
            TicketPriority::Urgent => 1,
            TicketPriority::High   => 2,
            TicketPriority::Normal => 4,
            TicketPriority::Low    => 8,
        };
    }
}
