<?php

namespace App\Services\Support;

use App\Enums\TicketStatus;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Phase 3 — Handles ticket assignment to an admin user.
 * Single responsibility: the assignment action only.
 */
class TicketAssignmentService
{
    public function __construct(
        private readonly TicketAuditLogger   $auditLogger,
        private readonly AdminActivityLogger  $activityLogger,
    ) {}

    /**
     * Assign a ticket to an admin user.
     * If the ticket was Open, moves it to InProgress.
     * If the ticket is already assigned, records a re-assignment.
     *
     * @param  SupportTicket $ticket
     * @param  User          $assignee     The admin being assigned
     * @param  User          $actingAdmin  The admin performing the action (may be the same)
     */
    public function assign(SupportTicket $ticket, User $assignee, User $actingAdmin): void
    {
        DB::transaction(function () use ($ticket, $assignee, $actingAdmin) {
            $previousAssignee = $ticket->assigned_to
                ? optional($ticket->assignedTo)->name
                : null;

            $ticket->assignTo($assignee);
            $ticket->updated_by = $actingAdmin->id;
            $ticket->save();

            $this->auditLogger->logAdmin(
                ticket: $ticket,
                admin: $actingAdmin,
                action: 'assigned',
                description: "Ticket assigned to {$assignee->name}.",
                fromValue: $previousAssignee,
                toValue: $assignee->name,
            );

            try {
                $this->activityLogger->logAdmin(
                    admin: $actingAdmin,
                    action: 'ticket_assigned',
                    description: "Assigned ticket {$ticket->ticket_number} to {$assignee->name}.",
                    subject: $ticket,
                    subjectLabel: $ticket->ticket_number,
                );
            } catch (\Throwable) {}
        });
    }
}
