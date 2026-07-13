<?php

namespace App\Policies;

use App\Models\Delegate;
use App\Models\SupportTicket;

/**
 * Phase 3 — Gates delegate portal access to SupportTicket actions.
 * Admin authorization is handled separately via the 'permission:support' middleware.
 */
class SupportTicketPolicy
{
    /** Delegate may view a ticket only if they own it. */
    public function view(Delegate $delegate, SupportTicket $ticket): bool
    {
        return $ticket->delegate_id === $delegate->id;
    }

    /** Delegate may reply only if they own the ticket AND the status allows it. */
    public function reply(Delegate $delegate, SupportTicket $ticket): bool
    {
        return $this->view($delegate, $ticket) && $ticket->canDelegateReply();
    }
}
